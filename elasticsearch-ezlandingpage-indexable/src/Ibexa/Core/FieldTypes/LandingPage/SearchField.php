<?php

/**
 * Author @ David Sayre
 * Repo: https://github.com/davidsayre/ibexa-dxp-snippets
 */

declare(strict_types=1);

namespace App\Ibexa\Core\FieldType\LandingPage;

use Ibexa\Contracts\Core\FieldType\Indexable;
use Ibexa\Contracts\Core\Persistence\Content\Field;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Contracts\Core\Search;

/**
 * Indexable definition for LandingPage field type.
 */
class SearchField implements Indexable
{
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition)
    {
        $blockText = $this->extractZoneBlockAttributeText($field->value->externalData);

        return [
            new Search\Field(
                'block_text',
                $blockText,
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                'block_text',
                $blockText,
                new Search\FieldType\FullTextField()
            ),
        ];
    }

    public function getIndexDefinition()
    {
        return [
            'block_text' => new Search\FieldType\StringField(),
        ];
    }

    /**
     * Get name of the default field to be used for matching.
     *
     * As field types can index multiple fields (see MapLocation field type's
     * implementation of this interface), this method is used to define default
     * field for matching. Default field is typically used by Field criterion.
     *
     * @return string
     */
    public function getDefaultMatchField()
    {
        return 'block_text';
    }

    /**
     * Get name of the default field to be used for sorting.
     *
     * As field types can index multiple fields (see MapLocation field type's
     * implementation of this interface), this method is used to define default
     * field for sorting. Default field is typically used by Field sort clause.
     *
     * @return string
     */
    public function getDefaultSortField()
    {
        return 'block_text';
    }

    /**
     * Simple loop over all zones, blocks, attributes searching for richtext <?xml> and extracting usable text into simple string
     * This is written to be very careful of deep arrays because the structures may change
     * @param $externalData
     * @return string
     */
    private function extractZoneBlockAttributeText($externalData) {
        $blockValues = array();
        if(is_array($externalData) && array_key_exists('zones', $externalData)) {
            foreach ($externalData['zones'] as $zone) {
                if (is_array($zone) && array_key_exists('blocks',$zone)) {
                    foreach ($zone['blocks'] as $block) {
                        if (is_array($block) && array_key_exists('attributes',$block)) {
                            foreach($block['attributes'] as $attribute) {
                                if(array_key_exists('value',$attribute)) {
                                    $value = $attribute['value'];
                                    // look for richtext and extract the simple strings
                                    // could detect and extract other block attribute values in the future
                                    // we don't want csv, doubles, integers, content_id, location_id etc..
                                    if(stripos($value,'<?xml') !== false) {
                                        $blockValues[] = strip_tags($value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // convert to simple " " delim string
        return implode(" ",$blockValues);
    }
}
