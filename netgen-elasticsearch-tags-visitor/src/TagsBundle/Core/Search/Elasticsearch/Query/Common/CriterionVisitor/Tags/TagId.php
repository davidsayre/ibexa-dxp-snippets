<?php

declare(strict_types=1);

namespace App\TagsBundle\Core\Search\Elasticsearch\Query\Common\CriterionVisitor\Tags;

use App\TagsBundle\Core\Search\Elasticsearch\Query\Common\CriterionVisitor\TagsVisitor;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Elasticsearch\Query\CriterionVisitor;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL;
use Ibexa\Elasticsearch\Query\CriterionVisitor\Iterator\FieldCriterionTargetIterator;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId as TagIdCriterion;

final class TagId extends TagsVisitor implements CriterionVisitor
{

    public function supports(Criterion $criterion, LanguageFilter $languageFilter): bool
    {
        if ($criterion instanceof TagIdCriterion) {
            switch ($criterion->operator) {
                case Criterion\Operator::EQ:
                case Criterion\Operator::IN:
                    return true;
                default:
                    return false;
            }
        }

        return false;
    }

    public function canVisit(Criterion $criterion): bool
    {
        return $criterion instanceof TagIdCriterion;
    }

    // NOTE: Elasticsearch 'should' means logical OR and 'must' is AND
    // NOTE: The TagId Criterion expects an ARRAY of ID's even if it's 1 item

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, LanguageFilter $languageFilter): array
    {

        // Option 1) Any class and tags fields ()
        if(empty($criterion->target)) {
            return $this->queryAllClassAllFields($criterion)->toArray();
        }

        // Option 2) Any class this field (tags)
        if(count( explode("/",$criterion->target) ) == 1) {
            return $this->queryByAnyClassThisField($criterion)->toArray();
        }

        // Option 3) 'this class this field' syntax (tags)
        if(count( explode("/",$criterion->target) ) == 2) {
            return $this->queryThisClassThisField($criterion)->toArray();
        }

    }

    protected function queryThisClassThisField($criterion) {
        $query = new QueryDSL\BoolQuery();
        $classFieldTarget = explode("/",$criterion->target);
        $query->addFilter(new QueryDSL\TermsQuery($classFieldTarget[0] . '_' . $classFieldTarget[1] . '_'.$this->fieldName.'_mi', $criterion->value));
        return $query;
    }

    protected function queryByAnyClassThisField($criterion) {
        $query = new QueryDSL\BoolQuery();
        $fields = $this->getSearchFields($criterion);
        foreach ($fields as $fieldName => $fieldObject) {
            $query->addShould(new QueryDSL\TermsQuery($fieldName, $criterion->value));
        }
        return $query;
    }

    protected function queryAllClassAllFields($criterion) {
        $query = new QueryDSL\BoolQuery();
        $fields = $this->getSearchFields($criterion);
        foreach ($fields as $fieldName => $criterionValue) {
            $query->addShould(new QueryDSL\TermsQuery($fieldName, $criterion->value));
        }
        return $query;
    }

}

