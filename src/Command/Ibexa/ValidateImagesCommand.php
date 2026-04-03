<?php

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Repository\Values\Filter\Filter;
use Ibexa\Contracts\Core\Variation\VariationHandler;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;

class ValidateImagesCommand extends Command
{

    protected Connection $connection;
    protected ContentService $contentService;
    protected Repository $repository;
    protected LoggerInterface $logger;
    protected VariationHandler $imageVariationHandler;

    protected InputInterface $input;
    protected OutputInterface $output;

    public const COMMAND_NAME = 'app:validate-images';

    private array $reportItems = [];

    private $contentTable = 'ezcontentobject';
    private $contentFieldTable = 'ezcontentobject_attribute';

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection       $connection,
        Repository       $repository,
        ContentService   $contentService,
        VariationHandler $imageVariationHandler,
        LoggerInterface  $validateImageLogger,
    )
    {
        parent::__construct(self::COMMAND_NAME);
        $this->connection = $connection;
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->imageVariationHandler = $imageVariationHandler;
        $this->logger = $validateImageLogger;

    }


    protected function configure(): void
    {
        $this
            ->setDescription("Validate 'image' content ")
            ->addOption(
                'content_id',
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
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $contentId = $input->getOption('content_id');
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $contentId = $input->getOption('content_id');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));

        $output->writeln('Running ..');
        $output->writeln('');

        // part 1: check for content missing names
        if (!empty($contentId) && is_numeric($contentId)) {
            $contentList = [$this->getContentById($contentId)];
        } else {
            $contentList = $this->queryImages($offset, $limit);
        }


        $count = 0 + $offset;
        /** @var Content $content */
        foreach ($contentList as $content) {
            $count++;

            $contentTypeIdentifier = $content->getContentType()->identifier;
            $contentId = $content->id;
            $contentName = $content->getName();
            $output->writeln(sprintf("Row %d - Check %s: %s '%s'", $count, $contentTypeIdentifier, $contentId, $contentName));

            $reportItem = $this->initReportItem();
            $reportItem->contentId = $contentId;
            $reportItem->contentTypeIdentifier = $contentTypeIdentifier;
            $reportItem->contentName = $contentName;

            foreach ($content->getFields() as $field) {
                if (array_search($field->fieldTypeIdentifier, ['ezimage', 'ibexa_image']) !== false) {
                    // only track image fields
                    $reportFieldItem = $this->initReportFieldItem();
                    $this->testImageField($field, $content, $reportFieldItem);
                    $reportItem->fields[] = $reportFieldItem;
                }
            }

            // apenend to simple holder
            $this->reportItems[] = $reportItem;
        }


        $this->displayReport();

        // Summary:
        echo "Query: count: " . $count . " offset: " . $offset . " limit: " . $limit . "\n";
        return Command::SUCCESS;

    }

    protected function queryImages(int $offset, int $limit)
    {
        $filter = new Filter();
        $filter->withCriterion(new Criterion\ContentTypeIdentifier('image'))
            ->withLimit($limit)
            ->withOffset($offset);
        $contentList = $this->contentService->find($filter, []);
        return $contentList;
    }

    protected function testImageField($field, Content $imageContent, $reportFieldItem)
    {
        // DEBUG $this->output->writeln(sprintf(" Field [%s] %d '%s'", $field->fieldTypeIdentifier, $field->id, $field->getFieldDefinitionIdentifier()));
        $row = $this->queryRawContentAttributeData($field->id);
        $dataText = $row['data_text'];

        $reportFieldItem->fieldId = $field->id;
        $reportFieldItem->fieldName = $field->getFieldDefinitionIdentifier();
        $reportFieldItem->dataText = $dataText;

        // check XML string
        $xml = simplexml_load_string($dataText);
        // $this->output->writeln(sprintf("  Raw Data: %s", $dataText));

        // check valid XML
        if (empty($xml->attributes()->width)) {
            //$this->output->writeln(" [error] xml missing width attribute value");
            $reportFieldItem->error = "invalid width xml value";
            return;
        }
        if (empty($xml->attributes()->height)) {
            //$this->output->writeln(" [error] xml missing height attribute value");
            $reportFieldItem->error = "invalid height xml value";
            return;
        }

        // TODO: check image file exists

        // TODO: try and correct attributes based on real file (must exist)

        // Try generating image variation
        $variation = $this->imageVariationHandler->getVariation(
            $field, $imageContent->getVersionInfo(), 'medium'
        );
        if (empty($variation->uri)) {
            $reportFieldItem->error = "invalid imageVariation uri";
            return;
        }
        // $this->output->writeln(sprintf("  Variation [%s]", $variation->uri));
    }

    protected function queryRawContentAttributeData($id)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*');
        $qb->from($this->contentFieldTable);
        $qb->where('id = :id');
        $qb->setParameter('id', $id);
        $result = $qb->execute()->fetchAllAssociative();
        return $result[0];
    }

    /**
     * Lazy object storage
     * @return StdClass
     */
    private function initReportItem()
    {
        $reportItem = new \StdClass();
        $reportItem->contentId = 0;
        $reportItem->contentTypeIdentifier = "";
        $reportItem->contentName = "";
        $reportItem->fields = [];
        return $reportItem;
    }

    /**
     * Lazy object storage
     * @return StdClass
     */
    private function initReportFieldItem()
    {
        $reportFieldItem = new StdClass();
        $reportFieldItem->fieldId = 0;
        $reportFieldItem->fieldName = '';
        $reportFieldItem->dataText = '';
        $reportFieldItem->error = '';
        return $reportFieldItem;
    }

    protected function displayReport()
    {
        // TODO: nice output of issues found
        foreach ($this->reportItems as $reportItem) {
            foreach ($reportItem->fields as $reportFieldItem) {
                if(!empty($reportFieldItem->error)) {
                    $this->output->writeln(sprintf("[error] Content %s / Field %d %s %s %s", $reportItem->contentId, $reportFieldItem->fieldId, $reportFieldItem->fieldName, $reportFieldItem->error, $reportFieldItem->dataText));
                } else {
                     // DEBUG: $this->output->writeln(sprintf('[ok] Field %d %s', $reportFieldItem->fieldId, $reportFieldItem->fieldName));
                }
            }
        }
    }

    protected function getContentById($contentId)
    {
        return $this->repository->sudo(
            function () use ($contentId) {
                return $this->contentService->loadContent($contentId);
            }
        );
    }

}

?>