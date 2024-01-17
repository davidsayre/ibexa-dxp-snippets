<?php

declare(strict_types=1);


use App\Form\Admin\Reports\ReportContentActivityType;
use App\Model\Admin\Reports\ReportContentActivityParams;
use App\Services\Admin\Reports\ReportContentActivityService;
use App\Services\Admin\Reports\ReportContentExportService;
use GuzzleHttp\Stream\Stream;
use Ibexa\Bundle\IO\BinaryStreamResponse;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap3View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportContentActivityController extends AbstractController {

    const MAX_RESULTS = 25;

    protected ReportContentActivityService $reportContentActivityService;
    protected ReportContentExportService $reportContentExportService;
    protected SearchService $searchService;

    public function __construct(
        ReportContentActivityService $reportContentActivityService,
        ReportContentExportService $reportContentExportService,
        SearchService $searchService
    ) {
        $this->reportContentActivityService = $reportContentActivityService;
        $this->reportContentExportService = $reportContentExportService;
        $this->searchService = $searchService;
    }

    public function index(Request $request) {

        $errors = [];
        $limit = $request->query->get('limit', self::MAX_RESULTS);
        $onPage = $request->query->get('page', 1);
        $offset = $request->query->get("offset") ?: 0;

        // defaults
        $emptyData = [
            'section_id'=>8
        ];

        $form = $this->createForm(ReportContentActivityType::class,$emptyData,['method' => 'GET']);

        $form->handleRequest($request);

        $params = array();
        $params['form'] = $form->createView();

        if($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();

            $searchParams = $this->paramFill($formData);
            $query = $this->reportContentActivityService->buildLocationQuery($searchParams);
            if(is_object($query)) {

                /** @var SubmitButton $clicked */
                $clicked = $form->getClickedButton();
                $buttonName = 'unknown';
                if(is_object($clicked)) {
                    $buttonName = $clicked->getName();
                }

                if($buttonName === ReportContentActivityType::BUTTON_DOWNLOAD_XML) {
                    // download XML rows from query
                    $filename = 'content_activity_report_results.xml';
                    $content = $this->reportContentExportService->generateRestXMLRowsByQuery($query, 1000);
                    $mimeType = "application/xml";
                    //$response = new StreamedResponse();
                    $response = new Response();
                    $response->setPrivate();
                    $response->setMaxAge(0);
                    $response->headers->set('Pragma', 'public');
                    $response->headers->set('Expires', '0');
                    $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
                    $response->headers->set('Content-Type', $mimeType);
                    $response->headers->set( 'Content-disposition', sprintf('filename=%s',$filename));
                    $response->setContent($content);
                    //$response->setCallback(function () use ($xmlRowText) {echo $xmlRowText;flush();return true;});
                    return $response;
                }

                if($buttonName === ReportContentActivityType::BUTTON_DOWNLOAD_CSV) {
                    $viewAddAttributes = ['language'];
                    $fieldAddAttributes = [];
                    // download XML rows from query
                    $filename = 'content_activity_report_results.csv';
                    $mimeType = 'text/csv';
                    $content = $this->reportContentExportService->generateCSVByQuery($query, $viewAddAttributes, $fieldAddAttributes, 1000);

                    //$response = new StreamedResponse();
                    $response = new Response();
                    $response->setPrivate();
                    $response->setMaxAge(0);
                    $response->headers->set('Pragma', 'public');
                    $response->headers->set('Expires', '0');
                    $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
                    $response->headers->set('Content-Type', $mimeType);
                    $response->headers->set( 'Content-disposition', sprintf('filename=%s',$filename));
                    $response->setContent($content);
                    return $response;
                }

                // continue to render paginated in-page results
                $query->limit = $limit;
                $pagerHolder = $this->buildPagerfantaHolder(
                    new LocationSearchAdapter(
                        $query,
                        $this->searchService
                    ),
                    $request,
                    $offset,
                    $limit
                );
                // COPY from holder
                $params['pagerfanta'] = $pagerHolder['pagerfanta'];

                // $params['paginationView'] = $pagerHolder['paginationView'];
                //dump($params);
            } else {
                $errors[] = "Unable to run query";
            }
        }

        if(!empty($errors)) {
            $params['errors'] = $errors;
        } // if errors

        return $this->render(
            "@standard/admin/reports/content_activity/dashboard.html.twig",
            $params
        );
    }

    public function downloadXmlContent(Request $request) {

        $contentId = 0;
        if($request->query->has('contentId')) {
            $contentId = $request->query->get('contentId');
            $contentId = intval($contentId);
        }
        if(!empty($contentId) && $contentId > 0) {
            try {
                $restContent = $this->reportContentExportService->generateRestXMLByContentId($contentId);
                $xmlContent = $this->reportContentExportService->convertRestContentToRestXml($restContent);
                $response = new Response($xmlContent,200,['Content-type: application/xml']);
                return $response;
            } catch (\Exception $e) {
                return new Response("Error with request");
            }
        }
        return new Response("Unable to process requested content");
    }

    // form param processing here
    protected function paramFill($formData){

        $searchParams = new ReportContentActivityParams();

        if(array_key_exists('date_field',$formData) && !empty($formData['date_field'])) {
            $searchParams->setDateField($formData['date_field']);
        }
        if(array_key_exists('date_from',$formData) && !empty($formData['date_from'])) {
            $searchParams->setDateFrom($formData['date_from']);
        }
        if(array_key_exists('date_to',$formData) && !empty($formData['date_to'])) {
            $searchParams->setDateTo($formData['date_to']);
        }
        if(array_key_exists('section_id',$formData) && !empty($formData['section_id'])) {
            $searchParams->setSectionId($formData['section_id']);
        }
        if(array_key_exists('exclude_location_ids',$formData) && !empty($formData['exclude_location_ids'])) {
            $searchParams->setExcludeLocationIds($formData['exclude_location_ids']);
        }
        if(array_key_exists('content_type',$formData) && !empty($formData['content_type'])) {
            $searchParams->setContentType($formData['content_type']);
        }
        if(array_key_exists('search_text',$formData) && !empty($formData['search_text'])) {
            $searchParams->setSearchText($formData['search_text']);
        }
        if(array_key_exists('title',$formData) && !empty($formData['title'])) {
            $searchParams->setTitle($formData['title']);
        }
        if(array_key_exists('tags',$formData) && is_numeric($formData['tags'])) {
            $searchParams->setTagIds([$formData['tags']]);// turn into array
        }
        if(array_key_exists('language_code',$formData) && !empty($formData['language_code'])) {
            $searchParams->setLanguageCode($formData['language_code']);
        }
        //$searchParams->setViewLanguage($formData['view_language']);
        if(array_key_exists('sort_dir',$formData) && !empty($formData['sort_dir'])) {
            $searchParams->setSortDir($formData['sort_dir']);
        }

        return $searchParams;
    }


    /**
     * Build Pagerfanta object and params from search results
     * @param array $searchResults
     * @return array
     */
    protected function buildPagerfantaHolder($adapter, Request $request, $offset = 0, $limit = self::MAX_RESULTS)    {

        // hard limit
        if($limit > self::MAX_RESULTS) {
            $limit = self::MAX_RESULTS;
        }

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage( $limit > 0 ? ($offset / $limit) + 1 : 1 );

        $paginationView = new TwitterBootstrap3View();
        $paginationOptions = array(
            'proximity' => 2,
            'prev_message' => "«",
            'next_message' => "»"
        );

        $paginationRouteGenerator = function($page) use ($request, $limit) {
            $queryStr = http_build_query(
                array_merge(
                    $request->query->all(),
                    array(
                        "offset" => ($page - 1) * $limit
                    )
                )
            );
            return $request->getBasePath() . "?{$queryStr}";
        };

        return array(
            "pagerfanta" => $pagerfanta,
            "paginationView" => $paginationView->render($pagerfanta, $paginationRouteGenerator, $paginationOptions)
        );
    }

}