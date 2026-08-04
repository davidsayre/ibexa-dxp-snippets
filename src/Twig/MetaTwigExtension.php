<?php

namespace App\Twig;

use Exception;
use Ibexa\Connector\Dam\FieldType\ImageAsset\Value as ImageAssetValue;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\FieldType\Image\Value as ImageValue;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Variation\VariationHandler;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use Ibexa\FieldTypeRichText\RichText\Converter\Html5;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MetaTwigExtension extends AbstractExtension
{

    private ContentService $contentService;
    protected VariationHandler $imageVariationHandler;
    protected Html5 $richTextOutputConverter;

    public function __construct(ContentService $contentService, VariationHandler $imageVariationHandler, Html5 $richTextOutputConverter)
    {
        $this->contentService = $contentService;
        $this->imageVariationHandler = $imageVariationHandler;
        $this->richTextOutputConverter = $richTextOutputConverter;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('meta_og_image', [$this, 'metaOGImage']),
            new TwigFunction('meta_og_description', [$this, 'metaOGDescription'])
        ];
    }

    /**
     * Extract asset field (ImageAsset) or image field and return image variation URI string
     * @param $content
     * @return string
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function metaOGImage($content): string
    {
        if (!($content instanceof Content)) {
            return '';
        }

        // try 1: content.asset.image
        /** @var ImageAssetValue $assetFieldValue */
        $assetField = $content->getField('asset');
        if (is_object($assetField)) {
            $assetFieldValue = $assetField->getValue();
            $assetContentId = intval($assetFieldValue->destinationContentId);
            if (!empty($assetContentId)) {
                // get 'image' from the image asset
                $imageContent = $this->contentService->loadContent($assetContentId);
                $imageField = $imageContent->getField('image');
                if (!empty($imageField->getValue())) {
                    // generate image variation
                    $variation = $this->imageVariationHandler->getVariation(
                        $imageField, $imageContent->getVersionInfo(), 'large'
                    );
                    return $variation->uri;
                }
            }
        }

        // Try 2: content.image
        $imageField = $content->getField('og_image');
        if (is_object($imageField)) {
            /** @var ImageValue $imageFieldValue */
            $imageFieldValue = $imageField->getValue();
            if (!empty($imageField->uri)) {
                $variation = $this->imageVariationHandler->getVariation(
                    $imageField, $content->getVersionInfo(), 'large'
                );
                return $variation->uri;
            }
        }

        // failsafe
        return "";
    }

    /**
     * Loop over richtext 'description' related fields
     * return HTML stripped string
     */
    public function metaOGDescription($content): string
    {
        if (!($content instanceof Content)) {
            return '';
        }
        $fieldTryOrder = ['meta_description', 'intro', 'summary'];
        foreach ($fieldTryOrder as $field) {
            try {
                /** @var Field $tryField */
                $tryField = $content->getField($field);
                if (is_object($tryField) && $tryField->getFieldTypeIdentifier() === 'ezrichtext') {
                    /** @var RichTextValue $richtextFieldValue */
                    $richtextFieldValue = $tryField->getValue();
                    if (!empty($richtextFieldValue)) {
                        $html = $this->richTextOutputConverter->convert($richtextFieldValue->xml)->saveHTML();
                        if (!empty($html)) {
                            return strip_tags($html);
                        }
                    }
                }
            } catch (Exception $e) {
                // ignore error
            }
        }
        return "";
    }
}


?>
