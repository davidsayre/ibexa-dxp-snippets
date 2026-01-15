<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check each content object's RichText and ImageAsset Fields for invalid relations
 *
 * example: bin/console app:validate-content:relations --limit=5000 --offset=0 --contentType-id=1
 */
class ValidateContentTypeCommand extends Command
{

    const STATUS_VALID = "valid";
    const STATUS_INVALID = "invalid";
    const STATUS_UNKNOWN = "unknown";
    const STATUS_ERROR = "error";

    const EMPTY_SERIALIZED_VALUE = "a:0:{}";

    /* serialized values are 0-based array count
        example: a:1:{s:6:"eng-US";s:4:"Blog";} means 2 elements
    */

    protected Connection $connection;
    protected ContentTypeService $contentTypeService;
    protected $repository;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;

    /** @var ContentTypeReportItem[] */
    protected array $reportItems = [];

    public const COMMAND_NAME = 'app:validate-content-type';

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection         $connection,
        Repository         $repository,
        ContentTypeService $contentTypeService,
        LoggerInterface    $validateContentLogger
    )
    {
        parent::__construct(self::COMMAND_NAME);
        $this->connection = $connection;
        $this->repository = $repository;
        $this->contentTypeService = $contentTypeService;
        $this->logger = $validateContentLogger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Validate content types(s)')
            ->addOption(
                'contenttype-id',
                null,
                InputOption::VALUE_REQUIRED,
                'specific content type ID; else query content by limit'
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
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $contentTypeId = $input->getOption('contenttype-id');
        if (!empty($contentTypeId) && !is_numeric($contentTypeId)) {
            throw new InvalidArgumentException('contentType_id optional value has to be an integer.');
        }
        $offset = $input->getOption('offset');
        if (!empty($offset) && !is_numeric($offset)) {
            throw new InvalidArgumentException('offset optional value has to be an integer.');
        }
        $limit = $input->getOption('limit');
        if (!empty($limit) && !is_numeric($limit)) {
            throw new InvalidArgumentException('limit optional value has to be an integer.');
        }

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $contentTypeId = $input->getOption('contenttype-id');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));

        $output->writeln('Running ..');
        $output->writeln('');

        $count = 0 + $offset;

        // step 1: check each row, then object
        $contentTypeRows = $this->queryContentTypeRows($offset, $limit, $contentTypeId);
        foreach ($contentTypeRows as $row) {
            // create temp object
            $reportItem = new ContentTypeReportItem();
            $reportItem->convertFromRow($row);
            $this->validateContentTypeRecord($reportItem);
            $this->validateContentTypeObject($reportItem);
            $this->validateContentTypeName($reportItem);
            // keyed index
            $this->reportItems[$reportItem->identifier] = $reportItem;
        }

        $this->validateContentTypeGroups();

        // check report
        $this->displayReport();

        // Summary:
        $this->output->writeln(sprintf("Query count: %d,  offset: %d, limit: %d", $count, $offset, $limit));

        return Command::SUCCESS;

    }

    protected function validateContentTypeGroups(): array
    {
        $contentTypeChoices = [];
        $this->output->writeln('<info>--- Check Groups ---</info>');
        // part 1: check groups are valid and names are valid
        foreach ($this->contentTypeService->loadContentTypeGroups() as $group) {
            $this->output->writeln(sprintf("Group: %s", $group->identifier));
            foreach ($this->contentTypeService->loadContentTypes($group) as $contentType) {
                // lookup reportItem to edit
                if (!array_key_exists($contentType->identifier, $this->reportItems)) {
                    // not found?
                    $this->output->writeln("Missing report item match");
                    continue;
                }
                $lookupReportItem = $this->reportItems[$contentType->identifier];
                $name = $contentType->getName($contentType->mainLanguageCode);
                if (empty($name)) {
                    $lookupReportItem->addError(sprintf(" . %s with mainLanguageCode %s has empty name [error]", $contentType->id, $contentType->mainLanguageCode));
                } else {
                    $lookupReportItem->addMessage(sprintf("in group %s", $group->identifier));
                }
                $contentTypeChoices[$contentType->id] = $name;
            }
        }

        // TODO: part 2: check every content type is IN at least 1 group

        return $contentTypeChoices;
    }

    protected function getContentTypeById($contentId)
    {
        return $this->repository->sudo(
            function () use ($contentId) {
                return $this->contentTypeService->loadContentType($contentId);
            }
        );
    }

    protected function validateContentTypeRecord(ContentTypeReportItem $reportItem)
    {
        if ($reportItem->serializedDescriptionList !== self::EMPTY_SERIALIZED_VALUE) {
            $trySerializedDescriptionList = @unserialize($reportItem->serializedDescriptionList);
            if (empty($trySerializedDescriptionList)) {
                $reportItem->addError("Serialized description list is invalid!");
            }
        }

        if ($reportItem->serializedNameList !== self::EMPTY_SERIALIZED_VALUE) {
            $trySerializedNameList = @unserialize($reportItem->serializedNameList);
            if (empty($trySerializedNameList)) {
                $reportItem->addError("Serialized name list is invalid!");
            }
        }

        // TODO: check for duplicate entries a:2:{s:6:"eng-US";s:11:"FAQ Article";s:16:"always-available";s:6:"eng-US";} has a duplicate
        // NOTE: unserialize() already de-dups
    }

    protected function validateContentTypeName($reportItem)
    {
        $contentNameRecord = $this->queryContentTypeNameRow($reportItem->id);
        if (empty($contentNameRecord)) {
            $reportItem->addError("Content type's name is missing!");
        }
        // TODO: get serializedName and get languages defined
        // TODO: compare serializedName (eng-US,eng-GB) to content name rows (eng-US,eng-GB)
        // TODO: check name's languageLocale (if multiple) vs contentType's languages
        // TODO: make sure language locale and language id are correct
    }

    protected function validateContentTypeObject(ContentTypeReportItem $reportItem)
    {
        try {
            /** @var ContentType $contentType */
            $contentType = $this->getContentTypeById($reportItem->id);
            // TODO: check fields on object
        } catch (\Exception $e) {
            $this->logger->error("Unable to initialize content type " . $reportItem->identifier);
            $this->logger->error($e->getMessage());
        }

        // TODO: check languages
        // TODO: check multi-language names
    }

    protected function queryContentTypeRows($offset, $limit, $contentTypeId)
    {
        // get set of contentIDs
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*');
        $qb->from('ezcontentclass');
        if (!empty($contentTypeId)) {
            $qb->where('id = :contentTypeId');
            $qb->setParameter('contentTypeId', $contentTypeId);
        }
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->execute()->fetchAllAssociative();
    }

    protected function queryContentTypeNameRow($contentTypeId)
    {
        // get set of contentIDs
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*');
        $qb->from('ezcontentclass_name');
        $qb->where('contentclass_id = :contentTypeId');
        $qb->setParameter('contentTypeId', $contentTypeId);
        return $qb->execute()->fetchAllAssociative();
    }

    protected function displayReport()
    {

        $this->output->writeln("<info>--- Report ---</info>");
        foreach ($this->reportItems as $item) {
            // show NOT valid entries
            if ($item->status !== self::STATUS_VALID) {
                $printLine = "";
                $printLine .= sprintf("ID: %s ", $item->id);
                $printLine .= sprintf("[<info>%s</info>] ", $item->identifier);
                $printLine .= sprintf("initialLanguageId: <info>%s</info> ", $item->initialLanguageId);
                $printLine .= sprintf("isContainer: %s ", $item->isContainer);
                $printLine .= sprintf("languageMask: %s ", $item->languageMask);

                $printLine .= $item->status . " ";

                if (count($item->messages) > 0) {
                    foreach ($item->messages as $message) {
                        $printLine .= sprintf("<info>%s</info> ", $message);
                    }
                }

                if (count($item->errors) === 0) {
                    $printLine .= sprintf("<info>OK</info> ");
                } else {
                    foreach ($item->errors as $error) {
                        $printLine .= sprintf("<error>%s</error> ", $error);
                    }
                }
                $this->output->writeLn($printLine);
            }
        }
        $this->output->writeln("<info>------</info>");
    }

}

// Temporary report item
class ContentTypeReportItem
{
    public $id = "";
    public $dbRow = []; // original database row
    public $contentObjectName = "";
    public $identifier = "";
    public $initialLanguageId = 0;
    public $isContainer = false;
    public $languageMask = 0;
    public $serializedDescriptionList = "";
    public $serializedNameList = "";
    public $urlAliasName = "";

    public $errors = [];
    public $messages = [];
    public $status = "";

    public function addError(string $message)
    {
        $this->errors[] = $message;
    }

    public function addMessage(string $message)
    {
        $this->messages[] = $message;
    }

    public function convertFromRow(array $row)
    {
        $this->id = $row['id'];
        $this->contentObjectName = $row['contentobject_name'];
        $this->identifier = $row['identifier'];
        $this->initialLanguageId = $row['initial_language_id'];
        $this->isContainer = $row['is_container'];
        $this->languageMask = $row['language_mask'];
        $this->serializedDescriptionList = $row['serialized_description_list'];
        $this->serializedNameList = $row['serialized_name_list'];
        $this->urlAliasName = $row['url_alias_name'];
        $this->dbRow = $row;
    }
}


?>