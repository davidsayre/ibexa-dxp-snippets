<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace App\Form\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\Iterator\BatchIterator;
use Ibexa\Contracts\Core\Repository\Iterator\BatchIteratorAdapter\ContentSearchAdapter;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\ContentName;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;

class TagChoiceDepth2Loader extends AbstractChoiceLoader
{
    protected TagsService $tagsService;
    protected $parentTags;

    public function __construct(
        TagsService $tagsService,
                    $parentTags
    ) {
        $this->tagsService = $tagsService;
        $this->parentTags = $parentTags;
    }

    protected function loadChoices(): array
    {
        if(empty($this->parentTags)) {
            return [];
        }
        try {
            $choices = $this->tagsService->sudo(function () {
                if(is_numeric($this->parentTags)) {
                    $this->parentTags = [$this->parentTags]; // convert to array
                }
                $tags = [];
                foreach($this->parentTags as $parentTagId) {
                    $parentTag = $this->tagsService->loadTag($parentTagId);
                    $children = $this->tagsService->loadTagChildren($parentTag);
                    foreach ($children as $childTag) {
                        $childKey = $parentTag->keyword ." > ".$childTag->keyword;
                        $grandChildren = $this->tagsService->loadTagChildren($childTag);
                        $tags[$childKey] = $childTag->id; // append child
                        foreach ($grandChildren as $grandChild) {
                            // build path label
                            $grandChildKey = $childKey . " > " .$grandChild->keyword;
                            $tags[$grandChildKey] = $grandChild->id; // append grand child
                        }
                    }
                }

                return $tags;
            });
            return $choices;
        } catch(\Exception $e) {
            // do nothing
        }
        return array();
    }
}
