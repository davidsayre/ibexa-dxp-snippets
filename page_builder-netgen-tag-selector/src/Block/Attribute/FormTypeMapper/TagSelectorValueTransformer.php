<?php

namespace App\Block\Attribute\FormTypeMapper;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Form\DataTransformerInterface;

use function array_key_exists;
use function count;
use function explode;
use function htmlspecialchars;
use function implode;

use const ENT_HTML401;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

class TagSelectorValueTransformer implements DataTransformerInterface {

    protected $tagsService;
    protected $languageCode;

    public function __construct(TagsService $tagsService, $languageCode)
    {
        $this->tagsService = $tagsService;
        $this->languageCode = $languageCode;
    }

    /**
     * storage to form
     * @param TagsValueHolder|null $value
     */
    public function transform($value): string
    {
        return $this->csvToHash($value);
    }

    /**
     * form to storage
     * @param mixed[]|null $value
     */
    public function reverseTransform($value): string
    {

        return $this->hashToCsv($value);
    }

    /**
     * @param array $tagIds
     * @return array
     */
    public function tagIdsToFieldHashes(array $tagIds = array()) {

        // clean string -> int
        $tagIds = $this->cleanTagArray($tagIds);

        $loadedTags = [];
        if (count($tagIds) > 0) {
            $loadedTags = $this->tagsService->loadTagList($tagIds)->toArray();
        }

        $ids = [];
        $parentIds = [];
        $keywords = [];
        $locales = [];

        /** @var Tag $tag */
        foreach($loadedTags as $tag) {
            $tagKeyword = $tag->getKeyword($this->languageCode);
            $mainKeyword = $tag->getKeyword($tag->mainLanguageCode);

            $ids[] = $tag->id;
            $parentIds[] = $tag->parentTagId;
            $keywords[] = $this->escape($tagKeyword ?? $mainKeyword);
            $locales[] = $tagKeyword !== null ? $this->languageCode : $tag->mainLanguageCode;
        }

        // storage holder for field renderings
        return [
            'ids' => implode('|#', $ids),
            'parent_ids' => implode('|#', $parentIds),
            'keywords' => implode('|#', $keywords),
            'locales' => implode('|#', $locales),
        ];
    }

    public function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    }

    /**
     * Convert csv to hash
     * @param $value
     * @return string
     */
    public function csvToHash($value) {
        $hash = implode("|#", explode(",", $value ?? ''));
        return $hash;
    }

    /**
     * Convert 1|#2|#3 --> 1,2,3
     * @param $value
     * @return string
     */
    public function hashToCsv($value) {
        $csv = implode(",", explode("|#", $value ?? ''));
        return $csv;
    }

    public function cleanTagArray($tagIds) {
        $tagIdsInt = array();
        foreach($tagIds as $tagId) {
            if ($tagId !== '0') {
                $tagIdsInt[] = (int) $tagId;
            }
        }
        return $tagIdsInt;
    }

}

?>