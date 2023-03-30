<?php

/**
 * Author @ David Sayre
 * Repo: https://github.com/davidsayre/ibexa-dxp-snippets
 */

declare(strict_types=1);

namespace Tests;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId as TagIdCriterion;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagKeyword as TagKeywordCriterion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// multiple subclasses used

/**
 * @Route("/_test_tags", name="_test_tags")
 */
class TestTagElasticsearchController extends AbstractController
{
    /**
     * @var SearchService
     */
    private $searchService;

    public function __construct(LocationService $locationService, SearchService $searchService)
    {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
    }

    /**
     * @Route("/", name="_index")
     * @Route("/id", name="_tag_id")
     */
    public function byTagIdAction(Request $request): Response
    {
        // TEST content is Press item in 2023 press tagged with allens pond (12) and Arizona (
        $parentLocationId = 62; // main pressroom
        /** @var Location $parentLocation */
        $parentLocation = $this->locationService->loadLocation($parentLocationId);
        $listParameters = array();
        //$listParameters['depth'] = 4;

        // Test by Tag ID
        $tagId = 12; // Allen's pond

        // TagId Option 1: class/field
        $results_tag_id_this_class_this_field = $this->searchByTagCriterion($parentLocation, new TagIdCriterion($tagId, 'article/tags'), $listParameters);

        // TagId Option 2: any class this tag field
        $results_tag_id_any_class_this_field = $this->searchByTagCriterion($parentLocation, new TagIdCriterion($tagId, 'tags'), $listParameters);

        // TagId option 3: any class and tag field
        $results_tag_id_any_class_any_tags = $this->searchByTagCriterion($parentLocation, new TagIdCriterion($tagId, null), $listParameters);

        return $this->render('@standard/test/search_tag_id.html.twig', array(
            'tag_id' => $tagId,
            'results_tag_id_this_class_this_field' => $results_tag_id_this_class_this_field,
            'results_tag_id_any_class_this_field' => $results_tag_id_any_class_this_field,
            'results_tag_id_any_class_any_tags' => $results_tag_id_any_class_any_tags
        ));
    }

    /**
     * @Route("/keyword", name="_tag_keyword")
     */
    public function byTagKeywordAction(Request $request): Response
    {

        // TEST content is Press item in 2023 press tagged with allens pond (12) and Arizona (
        $parentLocationId = 62; // main pressroom
        /** @var Location $parentLocation */
        $parentLocation = $this->locationService->loadLocation($parentLocationId);
        $listParameters = array();

        // Test by Keyword
        $tagkeyword = "Arcadia";

        // TagKeyword Option 1: class/field
        $results_tag_keyword_this_class_this_field = $this->searchByTagCriterion($parentLocation, new TagKeywordCriterion(Query\Criterion\Operator::LIKE, $tagkeyword, 'article/tags'), $listParameters);

        // TagId Option 2: any class this tag field
        $results_tag_keyword_any_class_this_field = $this->searchByTagCriterion($parentLocation, new TagKeywordCriterion(Query\Criterion\Operator::LIKE, $tagkeyword, 'tags'), $listParameters);

        // TagId option 3: any class and tag field
        $results_tag_keyword_any_class_any_field = $this->searchByTagCriterion($parentLocation, new TagKeywordCriterion(Query\Criterion\Operator::LIKE, $tagkeyword, null), $listParameters);

        return $this->render('@standard/test/search_tag_keyword.html.twig', array(
            'tag_keyword' => $tagkeyword,
            'results_tag_keyword_this_class_this_field' => $results_tag_keyword_this_class_this_field,
            'results_tag_keyword_any_class_this_field' => $results_tag_keyword_any_class_this_field,
            'results_tag_keyword_any_class_any_field' => $results_tag_keyword_any_class_any_field
        ));
    }


    /**
     * @param Location $parentLocation
     * @param $tagCriterion
     * @param array $listParameters
     * @return array
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    public function searchByTagCriterion($parentLocation, $tagCriterion, $listParameters)
    {
        $query = new LocationQuery();
        $mainAndCriteria = [
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
            new Query\Criterion\Subtree($parentLocation->pathString),
            $tagCriterion
        ];
        $query->filter = new Query\Criterion\LogicalAnd($mainAndCriteria);
        $query->sortClauses = [new SortClause\ContentName(LocationQuery::SORT_ASC)];

        $results = [];
        try {
            $search = $this->searchService->findLocations($query);

            foreach ($search->searchHits as $searchHit) {
                /**
                 * @var Content $contentObject
                 */
                $contentObject = $searchHit->valueObject;
                $results[] = $contentObject;
            }
        } catch (\Exception $ex) {
            //print_r("EXCEPTION!". $ex->getMessage());
        }
        return $results;
    }


}

?>