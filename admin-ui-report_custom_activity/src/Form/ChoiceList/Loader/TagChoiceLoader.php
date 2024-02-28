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

class TagChoiceLoader extends AbstractChoiceLoader
{
    protected TagsService $tagsService;
    protected $parentTagId;

    public function __construct(
        TagsService $tagsService,
        $parentTagId
    ) {
        $this->tagsService = $tagsService;
        $this->parentTagId = $parentTagId;
    }

    protected function loadChoices(): array
    {
        if(empty($this->parentTagId)) {
            return array();
        }
        try {
            $tags = $this->tagsService->sudo(function () {
                $parentTag = $this->tagsService->loadTag($this->parentTagId);
                $children = $this->tagsService->loadTagChildren($parentTag);
                $tags = [];
                foreach ($children as $childTag) {
                    $tags[$childTag->keyword] = $childTag->id;
                }
                return $tags;
            });
            return $tags;
        } catch(\Exception $e) {
            // do nothing
        }
        return array();
    }
}
