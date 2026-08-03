<?php

declare(strict_types=1);

namespace App\Twig;

use Exception;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

/**
 * Content lookup and manipulate helper twig functions
 */
class ContentHelperTwigExtension extends AbstractExtension
{


    private ContentService $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('contentid_to_content', [$this, 'contentIdTocontent']),
            new TwigFilter('contentid_to_locationid', [$this, 'contentIdToMainLocationId']),
            new TwigFilter('contentids_to_locationids', [$this, 'contentIdsToMainLocationIds']),
        ];
    }

    /**
     * This is a basic utility for getting the full content object from just the contentId
     * The use case is for converting an imageasset field's destinationContentId into the image content object
     * @param $contentId
     * @return Content|null
     */
    public function contentIdToContent($contentId): ?Content
    {
        if (empty($contentId)) {
            return null;
        }
        try {
            $contentId = intval($contentId);
            return $this->contentService->loadContent($contentId);
        } catch (Exception $e) {

        }
        return null;
    }

    /**
     * From: vendor/ibexa/fieldtype-page/src/lib/FieldType/Page/Block/Event/Listener/EmbedBlockListener.php
     * @param $contentId
     * @return int|null
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function contentIdToMainLocationId($contentId): ?int
    {
        $contentId = intval($contentId, 10);
        if (!$contentId) {
            return 0;
        }
        $contentInfo = $this->contentService->loadContentInfo($contentId);
        return $contentInfo->mainLocationId;
    }

    /**
     * @param $aContentIds
     * @return int[]
     */
    public function contentIdsToMainLocationIds($aContentIds): array
    {
        $aLocationIds = [];
        foreach ($aContentIds as $iContentId) {
            $iLocationId = $this->contentIdToMainLocationId(intval($iContentId));
            if ($iLocationId) {
                array_push($aLocationIds, $iLocationId);
            }
        }
        return $aLocationIds;
    }
}
