<?php


use App\Entity\ReportItemRichTextImage;
use App\Service\Admin\Reports\ReportContentRichTextImageService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Response;

class ReportContentRichTextImageController extends AbstractController {

    const MAX_RESULTS = 25;

    private ReportContentRichTextImageService $reportContentRichTextImageService;
    private EntityManagerInterface $em;

    public function __construct(ReportContentRichTextImageService $reportContentRichTextImageService, EntityManagerInterface $em) {
        $this->reportContentRichTextImageService = $reportContentRichTextImageService;
        $this->em = $em;
    }

    public function index(Request $request) {
        $currentPage = 1;
        if($request->query->getInt('page', 1)) {
            $currentPage = $request->query->getInt('page', 1);
        }

        // TODO: get page from url
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('r')
            ->from(ReportItemRichTextImage::class, 'r')
            ->orderBy('r.contentId', 'DESC');
        ;

        $adapter = new QueryAdapter($queryBuilder);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(self::MAX_RESULTS);
        $pagerfanta->setCurrentPage($currentPage);
        $params = [
            'pagerfanta' => $pagerfanta
        ];
        return $this->render(
            "@admin/reports/richtext_images/dashboard.html.twig",
            $params
        );
    }

    public function download(Request $request) {
        $rows = $this->reportContentRichTextImageService->queryReportItemsWithContentFields();

        $csvContent = '';
        $lines = 0;
        foreach ($rows as $row) {
            // get header
            if($lines === 0) {
                $csvContent .= implode(',', array_keys($row))."\n";
            }
            $csvLine = "";
            foreach($row as $field => $value) {
                $cleanValue = trim($value);
                $cleanValue = str_replace("'", "", $cleanValue);
                $cleanValue = str_replace('"', "", $cleanValue);
                /* @var string $cleanValue */
                if( is_numeric($cleanValue) && $cleanValue > 700000000) {
                    $date = new \DateTime();
                    $date->setTimestamp($cleanValue);
                    if (is_a($date, 'DateTime')) {
                        $csvLine .= '"' . $date->format('Y-m-d H:i:s') . '",';
                    }
                } else {
                    $csvLine .= '"' . $cleanValue . '",';
                }
            }
            $csvContent .= $csvLine . "\n"; // end line
            $lines++;
        }
        try {
            $filename = "report_items_image_aliases.csv";

            // Create a download response using disposition and file
            $response = new Response(
                $csvContent, 200,
                [
                    'Content-type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ]
            );
            return $response;
        } catch (\Exception $e) {
            return new Response("Error with request");
        }
    }

}

?>