<?php

/**
 * Author @ David Sayre
 * Repo: https://github.com/davidsayre/ibexa-dxp-snippets
 */

declare(strict_types=1);

namespace App\TagsBundle\Core\Search\Elasticsearch\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Persistence\Content\Type\Handler;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Core\Search\Common\FieldValueMapper;

use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\BoolQuery;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\TermQuery;
use Ibexa\Elasticsearch\Query\CriterionVisitor\Iterator\FieldCriterionTargetIterator;
use function array_merge;

abstract class TagsVisitor
{
    /**
     * For tag-queries which aren't field-specific.
     */
    protected Handler $contentTypeHandler;

    /**
     * Identifier of the field type that criterion can handle.
     * Elasticsearch field part: tag_ids | tag_keywords | parent_tag_ids | tag_text
     *  example result: (article)_(tags)_(tag_ids)_mi
     *  example result: (article)_(tags)_(tag_keywords)_ms
     * The ES field type building ( _ms | _mi ... ) is handled in the fieldNameResolver()
     */
    protected string $fieldTypeIdentifier;

    /**
     * Name of the field type's indexed field that criterion can handle.
     */
    protected string $fieldName;

    public function __construct(
        FieldNameResolver $fieldNameResolver,
        FieldValueMapper $fieldValueMapper,
        Handler $contentTypeHandler,
        string $fieldTypeIdentifier,
        string $fieldName
    ) {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->fieldValueMapper = $fieldValueMapper;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->fieldName = $fieldName;
    }

    /**
     * @param Criterion $criterion
     * @param $fieldFilter (optional filter fieldname> value returned
     * @return array
     */
    protected function getSearchFields(Criterion $criterion): array
    {
        // Target field provided, get all classes with target field
        if ($criterion->target !== null) {
            return $this->fieldNameResolver->getFieldTypes(
                $criterion,
                $criterion->target,
                $this->fieldTypeIdentifier,
                $this->fieldName
            );
        }

        // Target not provided, get all class and all 'eztags' ($this->fieldTypeIdentifier) field(s)
        $targetFieldTypes = [];
        foreach ($this->contentTypeHandler->getSearchableFieldMap() as $fieldDefinitions) {
            foreach ($fieldDefinitions as $fieldIdentifier => $fieldDefinition) {
                if (!isset($fieldDefinition['field_type_identifier'])) {
                    continue;
                }

                if ($fieldDefinition['field_type_identifier'] !== $this->fieldTypeIdentifier) {
                    continue;
                }

                $fieldTypes = $this->fieldNameResolver->getFieldTypes(
                    $criterion,
                    $fieldIdentifier,
                    $this->fieldTypeIdentifier,
                    $this->fieldName
                );
                // de-duplicate using absolute es field name keys
                foreach ($fieldTypes as $esFieldName => $value) {
                    $targetFieldTypes[$esFieldName] = $criterion->value;
                }
            }
        }

        return $targetFieldTypes;
    }
}
