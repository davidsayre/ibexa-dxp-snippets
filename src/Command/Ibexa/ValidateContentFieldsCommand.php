<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use Ibexa\Core\FieldType\ImageAsset\Value as ImageAssetValue;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check each content object's RichText and ImageAsset Fields for invalid relations
 *
 * example: bin/console app:validate-content:relations --limit=5000 --offset=0 --contentclass-id=1
 */
class ValidateContentFieldsCommand extends Command {

    const STATUS_VALID = "valid";
    const STATUS_INVALID = "invalid";
    const STATUS_UNKNOWN = "unknown";
    const STATUS_ERROR = "error";

    protected Connection $connection;
    protected $contentService;
    protected $repository;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;

    protected $reportItems = array();

    public const COMMAND_NAME = 'app:validate-content:relations';

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection $connection,
        Repository $repository,
        ContentService $contentService,
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

        ;
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

        $contentId = $input->getOption('content-id');
        $contentClassId = $input->getOption('contentclass-id');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));

        $output->writeln('Running ..');
        $output->writeln('');

        if(!empty($contentId) && is_numeric($contentId)) {
            $contentIdRows = array(array('id'=>$contentId));
        } else {
            $contentIdRows = $this->queryContentListByContentType($offset, $limit, $contentClassId);
        }

        $count = 0 + $offset;
        foreach($contentIdRows as $row) {
            $count++;
            /** @var Content $content */
            $content = $this->getContentById($row['id']);
            $logPrefix = "ContentId [".$content->id."] ".$content->getName();
            $output->writeln($logPrefix);
            // HOLD $this->logger->info($logPrefix);
            $this->checkContentFieldData($content);
        }

        // check report
        $this->displayReport();

        // Summary:
        echo "Query: count: ".$count." offset: ".$offset. " limit: ".$limit."\n";

        return Command::SUCCESS;

    }

    protected function checkContentFieldData(Content $content) {
        $sourceContentId = (int) $content->id;
        $fields = $content->getFields();
        foreach ($fields as $field) {
            $fieldRelations = [];
            $fieldIdentifier = $field->getFieldDefinitionIdentifier();
            $fieldType = $field->getFieldTypeIdentifier();
            switch ($fieldType) {
                case 'ezrichtext': {
                    try{
                        /** @var RichTextValue $value */
                        $value = $field->getValue();
                        $this->validateRichTextField($value->xml, $sourceContentId, $fieldIdentifier, $fieldType);
                        // echo "length: ".strlen($value->xml->saveXML()). " ";
                        $this->extractContentsFromRichtextLinks($value->xml, $sourceContentId, $fieldIdentifier, $fieldType);
                        $this->extractContentsFromRichTextEmbeds($value->xml, $sourceContentId, $fieldIdentifier, $fieldType);
                    } catch(\Exception $e) {
                        $this->output->writeln("ERROR processing XML ".$e->getMessage());
                    }
                    break;
                }
                case 'ezimageasset': {
                    /** @var ImageAssetValue $value */
                    $value = $field->getValue();
                    if(!empty($value->destinationContentId)) {
                        $fieldRelations[$fieldType.'_image_asset'] = [ $value->destinationContentId ];
                    }
                    break;
                }

                /** TODO other extractors
                 *
                ezbinaryfile
                ezboolean
                ezdatetime
                ezemail
                ezform
                ezgmaplocation
                ezidentifier
                ezimage
                ezimageasset
                ezinisetting
                ezinteger
                ezkeyword
                ezlandingpage
                ezobjectrelation
                ezobjectrelationlist
                ezoption
                ezrichtext
                ezselection
                ezstring
                eztags
                eztext
                ezurl
                 */

            }

        }

        // lookup if contentId's found in fields and if they are valid / published
        // If invalid, then log error
    }

    protected function getContentById($contentId) {
        return $this->repository->sudo(
            function () use ($contentId) {
                return $this->contentService->loadContent($contentId);
            }
        );
    }

    protected function validateRichTextField($xml, $sourceContentId, $fieldIdentifier, $fieldType)
    {
        try {
            $test = new RichTextValue($xml);
        } catch (\Exception $e) {
            $errorMessage = "RichText content_id: " . $sourceContentId . "[" . $fieldIdentifier . "] (".$fieldType.") ".$e->getMessage();
            $this->logger->error($errorMessage);
            $this->output->writeln("ERROR ".$errorMessage);
        }
    }
    protected function extractContentsFromRichtextLinks($xml, $sourceContentId, $fieldIdentifier, $fieldType) {
        // <link xlink:href="ezurl://49" xlink:show="none">
        $linkEles = $xml->getElementsByTagName('link');
        /** @var \DOMElement $linkEle */
        foreach($linkEles as $linkEle ) {
            $href = $linkEle->getAttribute('xlink:href');
            // check for ezlocation://00000
            if(stripos($href,'ezlocation') !== false) {
                //echo $href."\n";
                $destinationLocationId = str_replace('ezlocation://','',$href);
                if(is_numeric($destinationLocationId)) {
                    $item = $this->newReportItem();
                    $item->fieldIdentifier = $fieldIdentifier;
                    $item->fieldType = $fieldType;
                    $item->sourceContentId = $sourceContentId;
                    // No destinationContentId
                    $item->destinationLocationId = $destinationLocationId;
                    $item->message = $href;
                    $item->status = $this->queryLocationExists($destinationLocationId);
                    // append
                    $this->reportItems[] = $item;
                }
            } else {
                // normal link echo $href."\n";
            }
        }
    }

    protected function extractContentsFromRichTextEmbeds($xml, $sourceContentId, $fieldIdentifier, $fieldType) {
        $contentIds = [];
        // <ezembed xlink:href="ezcontent://24390" view="embed" ezxhtml:align="right" ezxhtml:class="ibexa-embed-type-image">
        $embedEls = $xml->getElementsByTagName('link');
        /** @var \DOMElement $embedEl */
        foreach($embedEls as $embedEl ) {
            $href = $embedEl->getAttribute('xlink:href');
            if(stripos($href,'ezcontent') !== false) {
                $destinationContentId = str_replace('ezcontent://','',$href);
                if(is_numeric($destinationContentId)) {
                    $item = $this->newReportItem();
                    $item->fieldIdentifier = $fieldIdentifier;
                    $item->fieldType = $fieldType;
                    $item->sourceContentId = $sourceContentId;
                    $item->destinationContentId = $destinationContentId;
                    // no destinationLocationId
                    $item->message = $href;
                    $item->status = $this->queryContentExists($destinationContentId);
                    // append
                    $this->reportItems[] = $item;
                }
            } else {
                // normal link echo $href."\n";
            }
        }
        return $contentIds;
    }


    protected function queryContentListByContentType($offset, $limit, $contentClassId){
        // get set of contentIDs
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id');
        $qb->from('ezcontentobject');
        if(!empty($contentClassId)){
            $qb->where('contentclass_id = :contentclass_id');
            $qb->setParameter('contentclass_id',$contentClassId);
        }
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->execute()->fetchAllAssociative();
    }

    protected function queryContentExists($contentId) {
        if(empty($contentId)) {
            return self::STATUS_UNKNOWN; // no check
        }
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id')
            ->from('ezcontentobject')
            ->where('id = :contentId')
            ->setParameter('contentId',$contentId);
        $result = $qb->execute()->fetchOne();
        if(!empty($result)){
            return self::STATUS_VALID;
        }
        return self::STATUS_INVALID;
    }

    protected function queryLocationExists($locationId) {
        if(empty($locationId)) {
            return self::STATUS_UNKNOWN; // no check
        }
        $qb = $this->connection->createQueryBuilder();
        $qb->select('node_id')
            ->from('ezcontentobject_tree')
            ->where('node_id = :locationId')
            ->setParameter('locationId',$locationId);
        $result = $qb->execute()->fetchOne();
        if(!empty($result)){
            return self::STATUS_VALID;
        }
        return self::STATUS_INVALID;
    }

    protected function displayReport() {
        echo "--- Checks ---\n";
        foreach ($this->reportItems as $item) {
            // show NOT valid entries
            if($item->status !== self::STATUS_VALID) {
                $message = "";
                $message .= "content_id: ".$item->sourceContentId." ";
                $message .= "[".$item->fieldIdentifier."] ";
                $message .= "(".$item->fieldType.") ";
                if(!empty($item->destinationContentId)) {
                    $message .= "dest_content_id: ".$item->destinationContentId." ";
                }
                if(!empty($item->destinationLocationId)) {
                    $message .= "dest_location_id: ".$item->destinationLocationId." ";
                }
                $message .= $item->status." ";
                if(!empty($item->error)){
                    $message .= $item->error." ";
                }
                // log errors to file
                // $this->logger->error($message);
                $this->output->writeLn($message);
            }
        }
        echo "--- ------ ---\n";
    }

    private function newReportItem()
    {

        $item = new \StdClass();
        $item->sourceContentId = "";
        $item->destinationContentId = "";
        $item->destinationLocationId = "";
        $item->fieldIdentifier = "";
        $item->fieldType = "";
        $item->error = "";
        $item->message = "";
        $item->status = "";
        return $item;
    }
}


?>