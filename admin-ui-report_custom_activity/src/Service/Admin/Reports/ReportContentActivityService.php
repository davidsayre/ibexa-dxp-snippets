<?php

namespace App\Services\Admin\Reports;

use App\Model\Admin\Reports\ReportContentActivityParams;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsMainLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion as NetgenCriterion;

class ReportContentActivityService {

    const DATE_FIELDS = array('published','modified');

    private LocationService $locationService;
    private ContentTypeService $contentTypeService;
    private SearchService $searchService;
    private ConfigResolverInterface $configResolver;
    private ParameterBagInterface $parameterBag;
    public function __construct(
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        SearchService $searchService,
        ConfigResolverInterface $configResolver,
        ParameterBagInterface $parameterBag
    )
    {
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
        $this->configResolver = $configResolver;
        $this->parameterBag = $parameterBag;
    }

    public function buildLocationQuery(ReportContentActivityParams $searchParams, $parentLocationId = null) {

        $mainAndCriteria = [];

        // get search parameters
        $sortDir = $searchParams->getSortDir();
        $dateField = $searchParams->getDateField();
        $dateFrom = $searchParams->getDateFrom();
        $dateTo = $searchParams->getDateTo();
        $sectionId = $searchParams->getSectionId();
        $excludeLocationIds = $searchParams->getExcludeLocationIds();
        $contentTypeId = $searchParams->getContentType();
        $searchText = $searchParams->getSearchText();
        $title = $searchParams->getTitle();
        $tagIds = $searchParams->getTagIds();
        $languageCode = $searchParams->getLanguageCode();

        switch ($sortDir) {
            case 'asc': {
                $sortDir = Query::SORT_ASC;
                break;
            }
            default : {
                $sortDir = Query::SORT_DESC;
                break;
            }
        }

        // default sort by name (will be overridden by date fields asap but failsafe
        $sort = [
            new SortClause\ContentName($sortDir)
        ];

        if(empty($parentLocationId)) {
            $parentLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
        }

        try {
            // limit to main location only
            $mainAndCriteria[] = new Query\Criterion\Location\IsMainLocation(IsMainLocation::MAIN);

            if(is_array($parentLocationId)) {
                $mainAndCriteria[] = new Query\Criterion\ParentLocationId($parentLocationId); // id array
            } else {
                // single ID
                $parentLocation = $this->locationService->loadLocation($parentLocationId);
                $mainAndCriteria[] = new Query\Criterion\Subtree($parentLocation->pathString);
            }

            // only filter if valid dates
            if(is_object($dateFrom) && is_object($dateTo)) {
                switch ($dateField) {
                    case 'published' : {
                        $mainAndCriteria[] = new Criterion\DateMetadata($dateField, Criterion\Operator::GTE, $dateFrom->getTimestamp());
                        $mainAndCriteria[] = new Criterion\DateMetadata($dateField, Criterion\Operator::LTE, $dateTo->getTimestamp());
                        $sort = [
                            new SortClause\DatePublished($sortDir)
                        ];
                        break;
                    }
                    case 'modified' : {
                        $mainAndCriteria[] = new Criterion\DateMetadata($dateField, Criterion\Operator::GTE, $dateFrom->getTimestamp());
                        $mainAndCriteria[] = new Criterion\DateMetadata($dateField, Criterion\Operator::LTE, $dateTo->getTimestamp());
                        $sort = [
                            new SortClause\DateModified($sortDir)
                        ];
                        break;
                    }
                    default : {
                        // custom (sort handled in content type)
                        if(!empty($dateField)) {
                            $mainAndCriteria[] = new Criterion\Field($dateField, Criterion\Operator::GTE, $dateFrom->getTimestamp());
                            $mainAndCriteria[] = new Criterion\Field($dateField, Criterion\Operator::LTE, $dateTo->getTimestamp());
                        }
                        break;
                    }
                }
            }

            if(!empty($excludeLocationIds)) {
                // convert to array if needed
                if(!is_array($excludeLocationIds)) {
                    $excludeLocationIds = explode(',',$excludeLocationIds);
                }
                foreach($excludeLocationIds as $excludeLocationId) {
                    $excludeLocation = $this->locationService->loadLocation($excludeLocationId);
                    $mainAndCriteria[] = new Criterion\LogicalNot(
                        new Criterion\Subtree($excludeLocation->pathString)
                    );
                }
            }

            if(!empty($title)) {
                $mainAndCriteria[] = new Criterion\Field('title', Criterion\Operator::LIKE, $title);
            }

            if(!empty($sectionId) && is_numeric($sectionId)) {
                $mainAndCriteria[] = new Criterion\SectionId($sectionId);
            }

            if(!empty($contentTypeId) && is_numeric($contentTypeId)) {
                try {
                    $contentType = $this->contentTypeService->loadContentType($contentTypeId);
                    $contentTypeIdentifier = $contentType->identifier;
                    if(array_search($dateField,['published','modified']) === false) {
                        $sort = [
                            new SortClause\Field($contentTypeIdentifier,$dateField, $sortDir)
                        ];
                    }
                } catch(\Exception $e) {
                }

                $mainAndCriteria[] = new Criterion\ContentTypeId($contentTypeId);
            }

            if (is_array($tagIds) && count($tagIds) > 0) {
                $mainAndCriteria[] = new NetgenCriterion\TagId($tagIds);
            }

            if(!empty($languageCode)) {
                $mainAndCriteria[] = new Criterion\LanguageCode($languageCode, false);
            }

            /* HOLD for custom date
             $mainAndCriteria[] = new Criterion\Field(
                'publish_date',
                Criterion\Operator::BETWEEN,
                [1190000000, 1200000000]
                )
             */

            //$mainAndCriteria[] = new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE);

            // add all the filter criteria into the main query object
            $query = new LocationQuery();
            $query->sortClauses = $sort;
            $query->filter = new Criterion\LogicalAnd($mainAndCriteria);


            // Full text query
            if (!empty($searchText)) {
                $query->query = new Criterion\FullText($searchText);
            }


             //dump($query);

            return $query;

        } catch(\Exception $e) {
            dump($e);
        }

        return null;
    }

    /**
     * Run the query directly (not likely, as pagerfanta would be used in controller)
     * @param LocationQuery $query
     * @return SearchResult
     * @throws InvalidArgumentException
     */
    public function search($query) {
        return $this->searchService->findLocations($query);
    }
}

?>