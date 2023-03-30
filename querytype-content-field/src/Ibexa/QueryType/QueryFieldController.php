<?php

namespace App\Controller\Ibexa\QueryType;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Core\MVC\Symfony\Controller\Content\QueryController;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Ibexa\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use Ibexa\Core\Pagination\Pagerfanta\LocationSearchHitAdapter;
use Ibexa\Core\Pagination\Pagerfanta\Pagerfanta;
use Ibexa\Core\QueryType\QueryParameterContentViewQueryTypeMapper;
use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A content view controller that runs queries based on the matched view configuration.
 *
 * The action used depends on which type of search is needed: location, content or contentInfo.
 */
class QueryFieldController extends QueryController
{

    private $searchService;

    private $contentViewQueryTypeMapper;

    public function __construct(
        QueryParameterContentViewQueryTypeMapper $contentViewQueryTypeMapper,
        SearchService $searchService
    ) {
        $this->contentViewQueryTypeMapper = $contentViewQueryTypeMapper;
        $this->searchService = $searchService;
    }

    /**
     * Runs a content search.
     *
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \Ibexa\Core\MVC\Symfony\View\ContentView
     */
    public function contentQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findContent');

        return $view;
    }

    /**
     * Runs a location search.
     *
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \Ibexa\Core\MVC\Symfony\View\ContentView
     */
    public function locationQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findLocations');

        return $view;
    }

    /**
     * Runs a contentInfo search.
     *
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \Ibexa\Core\MVC\Symfony\View\ContentView
     */
    public function contentInfoQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findContentInfo');

        return $view;
    }

    /**
     * Runs the Query defined in $view using $method on SearchService.
     *
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param string $method Name of the SearchService method to run.
     */
    private function runQuery(ContentView $view, $method)
    {
        $searchResults = $this->searchService->$method(
            $this->contentViewQueryTypeMapper->map($view)
        );
        $view->addParameters([$view->getParameter('query')['assign_results_to'] => $searchResults]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\Core\MVC\Symfony\View\ContentView
     */
    public function pagingQueryAction(ContentView $view, Request $request)
    {
        $this->runPagingQuery($view, $request);

        return $view;
    }

    /**
     * DJS custom query builder with special view.yaml 'sort_field' param
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function runPagingQuery(ContentView $view, Request $request)
    {
        $queryParameters = $view->getParameter('query');

        $limit = $queryParameters['limit'] ?? 10;
        $pageParam = $queryParameters['page_param'] ?? 'page';

        /** @var Query $query */
        $query = $this->contentViewQueryTypeMapper->map($view);

        // new special field sort syntax for 'sort_field'
        if(array_key_exists('sort_field',$queryParameters)) {
            $sortPattern = explode(" ",$queryParameters['sort_field']);
            // 'some_class/some_field asc'
            if(count($sortPattern) == 2){
                // 'some_class/some_field'
                $sortContentPattern = explode("/",$sortPattern[0]);
                if(count($sortContentPattern) === 2) {
                    $sortContentType = $sortContentPattern[0]; // some_class
                    $sortContentField = $sortContentPattern[1]; // some_field
                    $sortDirection = LocationQuery::SORT_ASC;
                    if(strtolower($sortPattern[1]) == 'desc') {
                        $sortDirection = LocationQuery::SORT_DESC;
                    }
                    $query->sortClauses = [new SortClause\Field($sortContentType,$sortContentField, $sortDirection)];
                }
            }
        }

        $page = $request->get($pageParam, 1);

        $pager = new Pagerfanta(
            $this->getAdapter($query)
        );

        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        $view->addParameters([$queryParameters['assign_results_to'] => $pager]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query $query
     *
     * @return \Pagerfanta\Adapter\AdapterInterface
     */
    private function getAdapter(Query $query): AdapterInterface
    {
        if ($query instanceof LocationQuery) {
            return new LocationSearchHitAdapter($query, $this->searchService);
        }

        return new ContentSearchHitAdapter($query, $this->searchService);
    }

}
