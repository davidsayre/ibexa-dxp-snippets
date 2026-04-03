<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use DOMElement;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use Ibexa\Core\FieldType\ImageAsset\Value as ImageAssetValue;
use Ibexa\Core\FieldType\TextLine\Value as TextLineValue;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use StdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Field as Field;


/**
 * Check each content object's RichText and ImageAsset Fields for invalid relations
 *
 * example: bin/console app:validate-content:relations --limit=5000 --offset=0 --contentclass-id=1
 */
class ValidateContentFieldsCommand extends Command
{

    const STATUS_VALID = "valid";
    const STATUS_INVALID = "invalid";
    const STATUS_UNKNOWN = "unknown";
    const STATUS_ERROR = "error";
    public const COMMAND_NAME = 'app:validate-content:fields';
    protected Connection $connection;
    protected $contentService;
    protected $repository;
    protected $logger;
    protected InputInterface $input;
    protected OutputInterface $output;
    protected $reportItems = array();
    private $ibexaVersion = 5;
    private $contentTable = "ibexa_content";
    private $contentTypeTable = 'ibexa_content_type';
    private $contentTypeNameTable = 'ibexa_content_type_name';
    private $contentTypeIdField = 'content_type_id';

    private $contentTreeTable = 'ibexa_content_tree';

    /**
     * @throws DBALException
     */
    public function __construct(
        Connection      $connection,
        Repository      $repository,
        ContentService  $contentService,
        LoggerInterface $validateContentLogger
    )
    {
        parent::__construct(self::COMMAND_NAME);
        $this->connection = $connection;
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->logger = $validateContentLogger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Validate Richtext')
            ->addOption(
                'content-id',
                null,
                InputOption::VALUE_REQUIRED,
                'specific Content ID; else query content by limit'
            )
            ->addOption(
                'offset',
                null,
                InputOption::VALUE_REQUIRED,
                'Query offset',
                0
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Query limit',
                10
            )
            ->addOption(
                'contentclass-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Query limit'
            )
            ->addOption('ibexa-version', null, InputOption::VALUE_OPTIONAL, 'IBEXA version', 5);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $contentId = $input->getOption('content-id');
        if (!empty($contentId) && !is_numeric($contentId)) {
            throw new InvalidArgumentException('content_id optional value has to be an integer.');
        }
        $offset = $input->getOption('offset');
        if (!empty($offset) && !is_numeric($offset)) {
            throw new InvalidArgumentException('offset optional value has to be an integer.');
        }
        $limit = $input->getOption('limit');
        if (!empty($limit) && !is_numeric($limit)) {
            throw new InvalidArgumentException('limit optional value has to be an integer.');
        }
        $contentClassId = $input->getOption('contentclass-id');
        if (!empty($contentClassId) && !is_numeric($contentClassId)) {
            throw new InvalidArgumentException('contentClass_id optional value has to be an integer.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        // hackery for now, hope to read from kernel instead
        $this->ibexaVersion = $this->input->getOption('ibexa-version');
        $this->ibexaVersionTableSwitcher();

        $contentId = $input->getOption('content-id');
        $contentClassId = $input->getOption('contentclass-id');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));

        $output->writeln('Running ..');
        $output->writeln('');

        if (!empty($contentId) && is_numeric($contentId)) {
            $contentIdRows = array(array('id' => $contentId));
        } else {
            $contentIdRows = $this->queryContentListByContentType($offset, $limit, $contentClassId);
        }

        $count = 0 + $offset;

        // Main processor
        foreach ($contentIdRows as $row) {
            $count++;
            /** @var Content $content */
            $content = $this->getContentById($row['id']);

            $reportItem = $this->newReportItem();
            $reportItem->name = $content->getName();
            $reportItem->contentId = $content->id;
            $reportItem->contentTypeIdentifier = $content->getContentType()->identifier;

            $this->checkContentFieldData($content, $reportItem);

            // append
            $this->reportItems[] = $reportItem;
        }

        // check report
        $this->displayReport();

        // Summary:
        $this->output->writeln(sprintf("Query count: %d,  offset: %d, limit: %d", $count, $offset, $limit));

        return Command::SUCCESS;

    }

    protected function ibexaVersionTableSwitcher()
    {
        if ($this->ibexaVersion < 5) {
            $this->contentTable = 'ezcontentobject';
            $this->contentTypeTable = 'ezcontentclass';
            $this->contentTypeNameTable = 'ezcontentclass_name';
            $this->contentTypeIdField = 'contentclass_id';
            $this->contentTreeTable = 'ezcontentobject_tree';
        }
    }

    protected function queryLocationExists($locationId)
    {
        if (empty($locationId)) {
            return self::STATUS_UNKNOWN; // no check
        }
        $qb = $this->connection->createQueryBuilder();
        $qb->select('node_id')
            ->from($this->contentTreeTable)
            ->where('node_id = :locationId')
            ->setParameter('locationId', $locationId);
        $result = $qb->execute()->fetchOne();
        if (!empty($result)) {
            return self::STATUS_VALID;
        }
        return self::STATUS_INVALID;
    }

    protected function queryContentListByContentType($offset, $limit, $contentClassId)
    {
        // get set of contentIDs
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id');
        $qb->from($this->contentTable);
        if (!empty($contentClassId)) {
            $qb->where(sprintf('%s = :contentclass_id', $this->contentTypeIdField));
            $qb->setParameter('contentclass_id', $contentClassId);
        }
        $qb->orderBy('id', 'ASC');
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->execute()->fetchAllAssociative();
    }

    protected function queryContentExists($contentId)
    {
        if (empty($contentId)) {
            return self::STATUS_UNKNOWN; // no check
        }
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id')
            ->from($this->contentTable)
            ->where('id = :contentId')
            ->setParameter('contentId', $contentId);
        $result = $qb->execute()->fetchOne();
        if (!empty($result)) {
            return self::STATUS_VALID;
        }
        return self::STATUS_INVALID;
    }

    protected function getContentById($contentId)
    {
        return $this->repository->sudo(
            function () use ($contentId) {
                return $this->contentService->loadContent($contentId);
            }
        );
    }

    protected function checkContentFieldData(Content $content, $reportItem)
    {

        $fields = $content->getFields();

        // TODO: check for content with duplicate fields on the same language and version number (is possible)

        /** @var Field $field */
        foreach ($fields as $field) {

            $fieldIdentifier = $field->getFieldDefinitionIdentifier();
            $fieldType = $field->getFieldTypeIdentifier();

            $reportFieldItem = $this->newReportFieldItem();
            $reportFieldItem->fieldIdentifier = $fieldIdentifier;
            $reportFieldItem->fieldType = $fieldType;

            switch ($fieldType) {
                case 'ezrichtext':
                case 'ibexa_richtext':
                {
                    try {
                        /** @var RichTextValue $value */
                        $value = $field->getValue();
                        $richTextXml = $value->xml;
                        $reportFieldItem->value = substr($richTextXml->textContent, 0, 25); // store truncated
                        $this->validateRichTextField($richTextXml, $reportFieldItem);
                        // echo "length: ".strlen($value->xml->saveXML()). " ";
                        $this->extractContentsFromRichtextLinks($richTextXml, $reportFieldItem);
                        $this->extractContentsFromRichTextEmbeds($richTextXml, $reportFieldItem);
                    } catch (Exception $e) {
                        $this->output->writeln("ERROR processing XML " . $e->getMessage());
                    }
                    break;
                }
                case 'ezimageasset':
                case 'ibexa_imageasset':
                {
                    $this->extractImageAssetField($field, $reportFieldItem);
                    break;
                }

                case 'ezstring':
                case 'ibexa_string' :
                {
                    /** @var TextLineValue $value */
                    $value = $field->getValue();
                    $reportFieldItem->value = substr($value->text, 0, 25); // store truncated
                    break;
                }

                /** TODO other extractors
                 *
                 * ezbinaryfile
                 * ezboolean
                 * ezdatetime
                 * ezemail
                 * ezform
                 * ezgmaplocation
                 * ezidentifier
                 * ezimage
                 * ezimageasset
                 * ezinisetting
                 * ezinteger
                 * ezkeyword
                 * ezlandingpage
                 * ezobjectrelation
                 * ezobjectrelationlist
                 * ezoption
                 * ezrichtext
                 * ezselection
                 * ezstring
                 * eztags
                 * eztext
                 * ezurl
                 */

            }

            $reportItem->fields[] = $reportFieldItem; // store
        }

        // lookup if contentId's found in fields and if they are valid / published
        // If invalid, then log error
    }

    private function newReportItem()
    {
        $item = new StdClass();
        $item->name = "";
        $item->contentId = "";
        $item->contentTypeIdentifier = "";
        $item->fields = [];
        $item->status = "";
        return $item;
    }

    /**
     * Child of reportItem
     */
    private function newReportFieldItem()
    {
        $item = new StdClass();
        $item->fieldIdentifier = "";
        $item->fieldType = "";
        $item->relatedItems = [];
        $item->value = ""; // truncated string
        $item->errors = [];
        $item->status = "";
        return $item;
    }


    /**
     * Child of reportFieldItem
     * @return StdClass
     */
    private function newReportFieldRelationItem()
    {
        $item = new StdClass();
        $item->destinationContentId = "";
        $item->destinationLocationId = "";
        $item->href = "";
        $item->status = "";

        return $item;
    }

    protected function validateRichTextField($xml, $reportFieldItem)
    {
        try {
            $test = new RichTextValue($xml);
        } catch (Exception $e) {
            $reportFieldItem->addError($e->getMessage());
            $this->logger->error($reportFieldItem->errors[] = $e->getMessage());
        }
    }

    protected function extractContentsFromRichtextLinks($xml, $reportFieldItem)
    {
        // <link xlink:href="ezurl://49" xlink:show="none">
        $linkEles = $xml->getElementsByTagName('link');
        /** @var DOMElement $linkEle */
        foreach ($linkEles as $linkEle) {
            $href = $linkEle->getAttribute('xlink:href');

            // check for ezlocation://00000
            if (stripos($href, 'ezlocation') !== false) {

                $reportFieldRelatedItem = $this->newReportFieldRelationItem();
                $reportFieldRelatedItem->href = $href;

                //echo $href."\n";
                $destinationLocationId = trim(str_replace('ezlocation://', '', $href));
                if (is_numeric($destinationLocationId)) {
                    $reportFieldRelatedItem->destinationLocationId = $destinationLocationId;
                }

                $reportFieldItem->relatedItems[] = $reportFieldRelatedItem; // store

            } else {
                // normal link echo $href."\n";
            }


        }
    }


    protected function extractContentsFromRichTextEmbeds($xml, $reportFieldItem)
    {
        $contentIds = [];
        // <ezembed xlink:href="ezcontent://24390" view="embed" ezxhtml:align="right" ezxhtml:class="ibexa-embed-type-image">
        $embedEls = $xml->getElementsByTagName('link');
        /** @var DOMElement $embedEl */
        foreach ($embedEls as $embedEl) {
            $href = $embedEl->getAttribute('xlink:href');
            if (stripos($href, 'ezcontent') !== false) {
                $destinationContentId = trim(str_replace('ezcontent://', '', $href));
                if (is_numeric($destinationContentId)) {
                    $fieldRelatedItem = $this->newReportFieldRelationItem();
                    $fieldRelatedItem->destinationContentId = $destinationContentId;
                    // no destinationLocationId
                    $fieldRelatedItem->href = $href;
                    $fieldRelatedItem->status = $this->queryContentExists($destinationContentId);
                    $reportFieldItem->relatedItems[] = $fieldRelatedItem; // store
                }
            } else {
                // normal link echo $href."\n";
            }
        }
        return $contentIds;
    }


    protected function extractImageAssetField($field, $reportFieldItem)
    {
        /** @var ImageAssetValue $value */
        $value = $field->getValue();
        if (!empty($value->destinationContentId)) {
            $destinationContentId = $value->destinationContentId;
            $fieldRelatedItem = $this->newReportFieldRelationItem();
            $fieldRelatedItem->destinationContentId = $destinationContentId;
            // no destinationLocationId
            $fieldRelatedItem->href = "image_asset";
            $fieldRelatedItem->status = $this->queryContentExists($destinationContentId);

            $reportFieldItem->relatedItems[] = $fieldRelatedItem; // store
        }
    }

    protected function displayReport()
    {
        echo "--- Checks ---\n";
        foreach ($this->reportItems as $reportItem) {
            $this->output->writeln(sprintf("[%s] [%s] '%s' has %s fields", $reportItem->contentId, $reportItem->contentTypeIdentifier, $reportItem->name, count($reportItem->fields)));
            foreach ($reportItem->fields as $reportFieldItem) {
                $this->output->writeln(sprintf(" [%s] %s : '%s ...'", $reportFieldItem->fieldType, $reportFieldItem->fieldIdentifier, $reportFieldItem->value));
                foreach ($reportFieldItem->relatedItems as $reportFieldRelatedItem) {
                    if (!empty($reportFieldRelatedItem->destinationLocationId)) {
                        $this->output->writeln(sprintf("  Location [%s] [%s] %s ", $reportFieldRelatedItem->destinationLocationId, $reportFieldRelatedItem->href, $reportFieldRelatedItem->status));
                    }
                    if (!empty($reportFieldRelatedItem->destinationContentId)) {
                        $this->output->writeln(sprintf("  Content [%s] [%s] %s ", $reportFieldRelatedItem->destinationContentId, $reportFieldRelatedItem->href, $reportFieldRelatedItem->status));
                    }
                }
            }
        }
        echo "--- ------ ---\n";
    }

}

?>