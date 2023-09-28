<?php
/**
 * Author: vidar.langseid@ibexa.co
 */
namespace App\Elasticsearch\Query\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Elasticsearch\Query\CriterionVisitor;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Elasticsearch\Query\CriterionVisitor\FullTextVisitor;


class FullTextVisitorDecorator implements CriterionVisitor
{
    private FullTextVisitor $inner;

    public function __construct(FullTextVisitor $inner)
    {
        $this->inner = $inner;
    }
    public function supports(Criterion $criterion, LanguageFilter $languageFilter): bool
    {
        return $this->inner->supports($criterion, $languageFilter);
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, LanguageFilter $languageFilter): array
    {
        $match = $this->inner->visit($dispatcher, $criterion, $languageFilter);

        // https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html#type-phrase
        $valueIsInQuotes = preg_match('/^".*"$/', $criterion->value);
        If ($valueIsInQuotes === 1) {
            $match['multi_match']['query'] = trim($criterion->value, '" ');
            unset($match['multi_match']["fuzziness"]);
            $match['multi_match']['type'] = 'phrase';
        }

        return $match;
    }
}