<?php

/**
 * Author: David Sayre / Allegiance Group
 */

declare(strict_types=1);

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateContentRichtextCommand extends Command
{

    protected Connection $connection;
    protected $contentService;
    protected $repository;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;

    public const COMMAND_NAME = 'app:validate-content:richtext';

    private $ibexaVersion = 5;
    private $contentTable = 'ibexa_content';
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

    protected function configure(): void
    {
        $this
            ->setDescription('Validate Content Richtext')
            ->addOption(
                'content-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Content ID'
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
                'content-type-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Content Type ID'
            )
            ->addOption('ibexa-version', null, InputOption::VALUE_OPTIONAL, 'IBEXA version', 5)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $contentId = $input->getOption('content-id');
        if (!empty($contentId) && !is_numeric($contentId)) {
            throw new InvalidArgumentException('content-id optional value has to be an integer.');
        }
        $offset = $input->getOption('offset');
        if (!empty($offset) && !is_numeric($offset)) {
            throw new InvalidArgumentException('offset optional value has to be an integer.');
        }
        $limit = $input->getOption('limit');
        if (!empty($limit) && !is_numeric($limit)) {
            throw new InvalidArgumentException('limit optional value has to be an integer.');
        }
        $contentTypeId = $input->getOption('content-type-id');
        if (!empty($contentTypeId) && !is_numeric($contentTypeId)) {
            throw new InvalidArgumentException('content-type-id optional value has to be an integer.');
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
        $contentTypeId = $input->getOption('content-type-id');
        $offset = $input->getOption('offset');
        $limit = $input->getOption('limit');

        if (empty($offset) || !is_numeric($offset)) {
            $offset = 0;
        }
        if (empty($limit) || !is_numeric($limit)) {
            $limit = 100;
        }

        $output->writeln('Running ..');
        $output->writeln('');

        $errors = array();

        if (!empty($contentId) && is_numeric($contentId)) {
            $contentList = array(array('id' => $contentId));
        } else {
            // get set of contentIDs
            $qb = $this->connection->createQueryBuilder();
            $qb->select('id');
            $qb->from($this->contentTable);
            if (!empty($contentTypeId)) {
                $qb->where(sprintf('%s = :contentclass_id',$this->contentTypeIdField));
                $qb->setParameter('contentclass_id', $contentTypeId);
            }
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);

            $contentList = $qb->execute()->fetchAllAssociative();
        }

        $count = 0 + $offset;
        foreach ($contentList as $content) {
            $count++;
            $cid = (int)$content['id'];
            /** @var Content $content */
            $content = $this->repository->sudo(
                function () use ($cid) {
                    return $this->contentService->loadContent($cid);
                }
            );
            $output->writeln("Row #" . $count . " Content [" . $cid . "] - " . $content->getName());
            $fields = $content->getFields();
            $output->writeln(sprintf("Found %s fields",count($fields)));
            foreach ($fields as $field) {
                $fieldIdentifier = $field->fieldDefIdentifier;
                $fieldTypeIdentifier = $field->fieldTypeIdentifier;
                $output->writeln(sprintf("Field [%s] %s", $fieldTypeIdentifier, $fieldIdentifier));
                if ($fieldTypeIdentifier === 'ezrichtext' || $fieldTypeIdentifier === 'ibexa_richtext') {
                    try {
                        // try the richtext
                        /** @var RichTextValue $value */
                        $value = $field->getValue();
                        print_r($value->xml->saveXML());
                        $output->write(" length: " . strlen($value->xml->saveXML()) . " ");
                        $test = new RichTextValue($value->xml);
                        $output->write("[ok]");
                    } catch (Exception $e) {
                        $output->writeln("error..");
                        $this->logger->error("content_id: " . $cid . " | field: " . $fieldIdentifier . "");
                        $errors[] = array('content_id' => $cid, 'field' => $fieldIdentifier);
                    }
                    $output->writeln("");
                }
            }
        }

        $output->writeln("Query offset " . $offset . " / limit " . $limit);

        // get content object
        // loop over field

        foreach ($errors as $error) {
            $output->writeln("ContentId : " . $error['content_id'] . " field: " . $error['field']);
        }

        return Command::SUCCESS;

    }

}

