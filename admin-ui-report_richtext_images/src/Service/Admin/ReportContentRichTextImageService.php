<?php

namespace App\Service\Admin\Reports;

use App\Entity\ReportItemRichTextImage;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Symfony\Component\Console\Output\OutputInterface;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;

/**
 * Generate report items from rich text fields
 * NOTE: This could be expanded to handle all types of embeds, not just images
 */
class ReportContentRichTextImageService
{

    private EntityManagerInterface $em;
    private Connection $connection;
    private ContentService $contentService;
    private UserService $userService;
    private PermissionResolver $permissionResolve;
    private OutputInterface $output;

    private $logLevel = 3; // 1 = process; 2 = sub process; 3 = content/item; 4 = field; 5 = values;
    private $save = false;

    /**
     * Truncate = true will remove all records from report item table and skip the inividual deletion logic
     * @var bool
     */
    private $truncateTable = false;

    private $imageContentTypes = ['image'];

    public function __construct(
        EntityManagerInterface $em,
        Connection             $connection,
        ContentService         $contentService,
        UserService            $userService,
        PermissionResolver     $permissionResolver
    )
    {
        $this->em = $em;
        $this->connection = $connection;
        $this->contentService = $contentService;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
    }

    // query report data with search and filter pagination
    // download report data (all)

    public function setLogLevel(int $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    public function setSave($save)
    {
        if (is_bool($save)) {
            $this->save = $save;
        }
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setTruncateTable(bool $truncateTable): void
    {
        $this->truncateTable = $truncateTable;
    }

    public function setImageContentTypes(array $contentTypes)
    {
        $this->imageContentTypes = $contentTypes;
    }

    public function loginUserOnProcess($username)
    {
        // set current user
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin($username)
        );
    }

    public function generateReportItemFromOneContent($contentId)
    {
        $this->log("Report items generating from 1 contentId ..");
        $results = [];
        if (!is_numeric($contentId)) {
            $this->log("contentID not number");
            return [];
        }
        $results[] = $this->parseContentToReportItems($contentId);
        $this->log("Report items [done]");
        return $results;
    }

    public function generateReportItems($limit, $offset)
    {
        $countResult = $this->queryCountContentWithRichText();
        $countResultTotal = $countResult->fetchOne();
        $this->log("Total content with richtext " . $countResultTotal, 2);

        $this->log("Report items generating ..", 2);
        $rows = $this->queryContentWithRichText($limit, $offset);
        $results = [];
        foreach ($rows as $row) {
            $contentId = $row['contentobject_id'];
            $results[] = $this->parseContentToReportItems($contentId);
        }
        $this->log("Report items [done]", 2);
        return $results;
    }

    protected function queryCountContentWithRichText()
    {
        $sql = "select count(distinct eco.id) as total
            from ezcontentobject eco, ezcontentobject_attribute ecoa
            where eco.id = ecoa.contentobject_id and eco.current_version = ecoa.version
            and ecoa.data_type_string = 'ezrichtext'";
        return $this->connection->executeQuery($sql);
    }

    /**
     * @param $limit
     * @param $offset
     * @return \Doctrine\DBAL\ForwardCompatibility\DriverResultStatement|\Doctrine\DBAL\ForwardCompatibility\DriverStatement|\Doctrine\DBAL\ForwardCompatibility\Result
     * @throws \Doctrine\DBAL\Exception
     */
    protected function queryContentWithRichText($limit = 1, $offset = 0)
    {
        $sql = "select distinct eco.id as contentobject_id
            from ezcontentobject eco, ezcontentobject_attribute ecoa
            where eco.id = ecoa.contentobject_id and eco.current_version = ecoa.version
            and ecoa.data_type_string = 'ezrichtext' order by eco.id limit :limit offset :offset";
        $sql = str_replace(":limit", $limit, $sql);
        $sql = str_replace(":offset", $offset, $sql);
        return $this->connection->executeQuery($sql);
    }

    protected function parseContentToReportItems($contentId)
    {
        $parseContentResults = [];
        $parseContentResults['content_id'] = $contentId;
        // can add more details into parseResults if needed
        try {
            $this->log(sprintf("Parse content [%s] ", $contentId), 3, false);
            $content = $this->contentService->loadContent($contentId);
            $this->log(sprintf("'%s' ", $content->getName()), 3); // end line
            $fields = $content->getFieldsByLanguage('eng-US');
            // content can have multiple richtext fields
            $contentReportItems = [];
            foreach ($fields as $field) {
                if ($field->fieldTypeIdentifier == 'ezrichtext') {
                    $reportItems = $this->extractReportItemsFromRichTextField($field, $content);
                    if (!empty($reportItems)) {
                        $contentReportItems[$field->getFieldDefinitionIdentifier()] = $reportItems;
                    }
                }
            }
            $parseContentResults['success'] = true;
        } catch (\Exception $e) {
            $this->log(sprintf("[error] %s", $e->getMessage()), 3); // end line
            $parseContentResults['error'] = true;
        }

        // only apend fields if not empty
        if (!empty($contentReportItems)) {
            $parseContentResults['fields'] = $contentReportItems;
        }
        return $parseContentResults;
    }

    protected function extractReportItemsFromRichTextField(Field $field, Content $content)
    {

        /** @var RichTextValue $fieldValue */
        $fieldValue = $field->getValue();
        $fieldDefIdentifier = $field->getFieldDefinitionIdentifier();

        $this->log(sprintf(" Extracting from field [%s] ", $fieldDefIdentifier), 4, false);
        if (!is_a($fieldValue, RichTextValue::class)) {
            return ['error' => "field value not richtext"];
        }

        $reportItems = [];

        $doc = $fieldValue->xml;
        $elements = $doc->getElementsByTagName('ezembed');
        $this->log(sprintf("found %s embeds", $elements->count()), 4);
        foreach ($elements as $element) {

            $linkContentId = $this->extractElementXlinkHrefContentId($element);
            if (!is_numeric($linkContentId) || $linkContentId == 0) {
                continue; // skip
            }

            $this->log(sprintf("  Embed content [%s] ", $linkContentId), 4, false);
            try {
                $sourceContent = $this->contentService->loadContent($linkContentId);
                $sourceContentType = $sourceContent->getContentType()->identifier;
                $this->log(sprintf("type [%s] ", $sourceContentType), 4, false);
            } catch (\Exception $e) {
                $this->log(sprintf("[error]"));
                continue; // skip
            }

            // Detect IMAGE content types
            if (array_search($sourceContentType, $this->imageContentTypes) !== false) {
                $reportItemRichTextImage = new ReportItemRichTextImage();

                // current content item and this field
                $reportItemRichTextImage->setContentType($content->getContentType()->identifier);
                $reportItemRichTextImage->setVersion($content->getVersionInfo()->versionNo);
                $reportItemRichTextImage->setLanguage($field->languageCode);
                $reportItemRichTextImage->setContentId($content->id); // ezcontentobject.id
                $reportItemRichTextImage->setFieldId($field->getId()); // ezcontentobject_attribute.id
                $reportItemRichTextImage->setFieldName($fieldDefIdentifier); // ezcontentclass_attribute.field_identifier

                // embedded image
                $reportItemRichTextImage->setImageContentId($sourceContent->id);

                // get embed image Alias (size)
                $reportItemRichTextImage->setImageAlias($this->extractElementEzConfigEzValueSize($element));
                $this->log(sprintf('alias [%s] ', $reportItemRichTextImage->getImageAlias()), 4, false);

                // get embed image align (if set)
                $reportItemRichTextImage->setImageAlign($this->extractElementAttribute($element, 'ezxhtml:align'));
                $this->log(sprintf('align [%s] ', $reportItemRichTextImage->getImageAlign()), 4, false);

                // get source content 'image' field and file (assuming this is what gets rendered)
                $imageFieldValue = $this->getImageFieldFileInfo($sourceContent);
                if (is_object($imageFieldValue)) {
                    if (property_exists($imageFieldValue, 'width') && is_numeric($imageFieldValue->width)) {
                        $reportItemRichTextImage->setImageRawWidth($imageFieldValue->width);
                        $this->log(sprintf('width [%s] ', $reportItemRichTextImage->getImageRawWidth()), 4, false);
                    }
                    if (property_exists($imageFieldValue, 'height') && is_numeric($imageFieldValue->height)) {
                        $reportItemRichTextImage->setImageRawHeight($imageFieldValue->height);
                        $this->log(sprintf('height [%s] ', $reportItemRichTextImage->getImageRawHeight()), 4, false);
                    }
                }
                $reportItems[] = $reportItemRichTextImage; // append to report holder
            }
            // FUTURE: other content types could be detected ..

            $this->log('', 4); // end line

        }
        return $reportItems;
    }


    /**
     * // extract contentId from example: <ezembed xlink:href="ezcontent://106" view="embed" ezxhtml:class="ibexa-embed-type-image" ezxhtml:align="left">
     * @param \DOMElement $element
     * @return array|int|string|string[]
     */
    protected function extractElementXlinkHrefContentId(\DOMElement $element)
    {
        if (!method_exists($element, 'getAttribute')) {
            $this->log("Element missing getAttribute()", 5);
            return 0;
        }
        try {
            $href = $element->getAttribute('xlink:href');
            if (stripos($href, 'ezcontent') !== false) {
                return str_replace('ezcontent://', '', $href);
            }
        } catch (\Exception $e) {
            // skip
        }

        return 0;
    }

    /**
     * Extract attribute from example: <ezembed xlink:href="ezcontent://106" view="embed" ezxhtml:class="ibexa-embed-type-image" ezxhtml:align="left">
     * @param \DOMElement $element
     * @return string
     */
    protected function extractElementAttribute(\DOMElement $element, $attributeName)
    {
        if (empty($attributeName)) {
            return "";
        }
        if (!method_exists($element, 'getAttribute')) {
            $this->log(sprintf("[error] %s missing getAttribute()", get_class($element)), 5);
            return "";
        }
        return $element->getAttribute($attributeName);
    }


    /**
     * Check if the <ezlink> is before (bad) or after (good) the <ezconfig>
     * @param \DOMElement $element
     * @return void
     */
    protected function checkElementHasInvalidEzLinkPosition(\DOMElement $element)
    {
        /** @var \DOMNode $childNode */
        foreach ($element->childNodes as $childNode) {
            print_r($childNode);
            // TODO:
        }
    }

    /**
     * Extract size from example: <ezconfig><ezvalue key="size">original</ezvalue></ezconfig>
     * @param $element
     * @return string
     */
    protected function extractElementEzConfigEzValueSize(\DOMElement $element)
    {
        if (!method_exists($element, 'getElementsByTagName')) {
            $this->log(sprintf("[error] %s missing getElementsByTagName()", get_class($element)), 5);
            return "";
        }
        $configElements = $element->getElementsByTagName('ezconfig');
        /** @var \DOMElement $configElement */
        foreach ($configElements as $configElement) {
            /** @var \DOMElement $valueElement */
            foreach ($configElement->childNodes as $valueElement) {
                $text = new \DOMText();
                if (!method_exists($valueElement, 'getAttribute')) {
                    $this->log(sprintf("[error] %s missing getAttribute()", get_class($valueElement)), 5);
                    continue;
                }
                if ($valueElement->getAttribute('key') == 'size') {
                    // found size
                    return $valueElement->nodeValue;
                }
            }
        }
        return "";
    }

    protected function getImageFieldFileInfo($content)
    {
        $sourceContentFields = $content->getFields();
        /** @var Field $field */
        foreach ($sourceContentFields as $field) {
            // stop at the 'image' field
            if ($field->fieldTypeIdentifier === 'ezimage' && $field->getFieldDefinitionIdentifier() === 'image') {
                return $field->getValue();
            }
        }
        return [];
    }

    public function purgeAllReportItems()
    {
        $sql = "truncate report_item_rich_text_image";
        $this->log(sprintf(" Truncate report items table"), 3, false);
        if ($this->save === true) {
            $this->connection->executeQuery($sql);
            $this->log(sprintf("[truncate]"));
        } else {
            $this->log(sprintf("[dry-run]"));
        }
        // also mark truncation happened for later operations
        $this->truncateTable = true;
    }


    public function storeReportResults($results)
    {
        $this->log("Parsing report results content > fields > report items ", 1);
        foreach ($results as $result) {
            $contentId = $result['content_id'];
            if ($this->truncateTable == false) {
                //Delete all existing report items for this content (wipe out) and no way to enforce uniqueness
                $sql = "delete from report_item_rich_text_image where content_id = :content_id";
                $sql = str_replace(":content_id", $contentId, $sql);
                $this->log(sprintf(" Delete all report items for content [%s] ", $contentId), 3, false);
                if ($this->save === true) {
                    //SKIP deleting individual records in favor of full truncation
                    $this->connection->executeQuery($sql);
                    $this->log(sprintf("[deleted]"));
                } else {
                    $this->log(sprintf("[dry-run]"));
                }
            }
            if (array_key_exists('fields', $result)) {
                $this->log(sprintf('  Store content [%s] ', $contentId), 3, false);
                foreach ($result['fields'] as $field => $reportItems) {
                    $this->log(sprintf("field [%s] ", $field), 3, false);
                    $saveCount = 0;
                    foreach ($reportItems as $reportItem) {
                        $saveCount++;
                        if ($reportItem instanceof ReportItemRichTextImage) {
                            if ($this->save === true) {
                                $this->em->persist($reportItem);
                                $this->log('.', 3, false);
                                if ($saveCount % 10 === 0) {
                                    $this->em->flush();
                                    $this->log("[save]", 3, false);
                                }
                            } else {
                                $this->log('d', 3, false);
                            }
                        }
                    }
                    $this->log("", 1); // end batch
                    if ($this->save === true) {
                        $this->em->flush();
                    }
                }
            }
        }
        if ($this->save === true) {
            $this->log(sprintf("Storing [done]"), 1);
        } else {
            $this->log(sprintf("Storing [dry-run]"), 1);
        }

    }

    public function queryReportItemsWithContentFields($limit = 100, $offset = 0)
    {
        $sql = "select 
            ri.id as ri_id,
            eco.name as content_name,            
            eco.published,
            eco.modified,
            ri.content_id,
            ri.content_type,
            eco.current_version,
            version as parsed_version,            
            ri.language, 
            ri.image_content_id,
            ri.image_alias,
            ri.image_raw_width,
            ri.image_raw_height,
            ri.image_alias,
            ri.image_align,
            eco.section_id
            from report_item_rich_text_image ri , ezcontentobject eco where ri.content_id = eco.id
            order by eco.id desc
            ";
        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * @param string $message
     * @param bool $newLine
     */
    protected function log($message = '', $logLevel = 3, $newLine = true)
    {
        if ($this->logLevel >= $logLevel) {
            if (is_object($this->output)) {
                if ($newLine) {
                    $this->output->writeln($message);
                } else {
                    $this->output->write($message);
                }
            } else {
                echo $message;
                if ($newLine) {
                    echo "\n";
                }
            }
        }
    }

}


?>