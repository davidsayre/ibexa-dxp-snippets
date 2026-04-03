<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateContentVersionCommand extends Command
{

    protected Connection $connection;
    protected $contentService;
    protected $repository;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;

    public const COMMAND_NAME = 'app:validate-content:versions';

    private $ibexaVersion = 5;
    private $contentTable = 'ibexa_content';
    private $contentNameTable = 'ibexa_content_name';
    private $contentVersionTable = 'ibexa_content_version';
    private $contentVersionVersionField = 'content_version';

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
            ->setDescription('Validate Content Versions')
            ->addOption(
                'content_id',
                'i',
                InputOption::VALUE_REQUIRED,
                'specific Content ID; else query content by limit'
            )
            ->addOption(
                'offset',
                'o',
                InputOption::VALUE_REQUIRED,
                'Query offset',
                0
            )
            ->addOption(
                'limit',
                'm',
                InputOption::VALUE_REQUIRED,
                'Query limit',
                10
            )
            ->addOption('ibexa-version', null, InputOption::VALUE_OPTIONAL, 'IBEXA version', 5);
    }

    protected function ibexaVersionTableSwitcher()
    {
        if ($this->ibexaVersion < 5) {
            $this->contentTable = 'ezcontentobject';
            $this->contentNameTable = 'ezcontentobject_name';
            $this->contentVersionTable = 'ezcontentobject_version';
            $this->contentVersionVersionField = 'version';
        }
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

        // hackery for now, hope to read from kernel instead
        $this->ibexaVersion = $this->input->getOption('ibexa-version');
        $this->ibexaVersionTableSwitcher();

        $contentId = $input->getOption('content_id');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));

        $output->writeln('Running ..');
        $output->writeln('');


        // part 1: check for content missing names
        if (!empty($contentId) && is_numeric($contentId)) {
            $contentMissingNameRows = array(array('id' => $contentId));
        } else {
            $contentMissingNameRows = $this->queryContentListMissingName($offset, $limit);
        }
        $count = 0 + $offset;
        foreach ($contentMissingNameRows as $row) {
            $count++;
            $content = $this->getContentById($row['id']);
            $this->logger->error("contentID: [" . $content->id . "] " . $content->getName() . " missing name record");
            $sql = "/* SQL */" . $this->generateSQLFixContentName($content);
            $output->writeln($sql);
            $this->logger->info($sql);
        }
        $output->writeln(sprintf("Query [%s] rows missing name, processed: %s, Offset %s, limit %s", count($contentMissingNameRows), $count, $offset, $limit));


        // part 2: check for content missing exact version matches
        if (!empty($contentId) && is_numeric($contentId)) {
            $contentNameMissingVersionRows = array(array('id' => $contentId));
        } else {
            $contentNameMissingVersionRows = $this->queryContentNameMissingVersion($offset, $limit);
        }
        $count = 0 + $offset;
        foreach ($contentNameMissingVersionRows as $row) {
            $count++;
            $this->logger->error("contentID: [" . $row['id'] . "] version [" . $row['version'] . "] name is missing version match");
            $sql = "/* SQL */ " . $this->generateSQLDeleteInvalidContentName($row['id'], $row['version']);
            $output->writeln($sql);
            $this->logger->info($this->generateSQLDeleteInvalidContentName($row['id'], $row['version']));
        }
        // Summary:
        $output->writeln(sprintf("Query [%s] rows invalid versions, processed: %s, Offset %s, limit %s", count($contentNameMissingVersionRows), $count, $offset, $limit));


        // Part 3: check for content with NO version
        if (!empty($contentId) && is_numeric($contentId)) {
            $contentMissingAnyVersionRows = array(array('id' => $contentId));
        } else {
            $contentMissingAnyVersionRows = $this->queryContentMissingAnyVersion($offset, $limit);
        }
        $count = 0 + $offset;
        foreach ($contentMissingAnyVersionRows as $row) {
            $count++;
            $this->logger->error("contentID: [" . $row['id'] . "] version [" . $row['version'] . "] is missing any version");
            // TODO insert
            $output->writeln($sql);
        }
        // Summary:
        $output->writeln(sprintf("Query [%s] rows invalid versions, processed: %s, Offset %s, limit %s", count($contentMissingAnyVersionRows), $count, $offset, $limit));

        // TODO: Part 3: check for names missing content (reverse)

        // TODO: Part 4: check for versions missing content (reverse)


        $output->writeln(">> Check the .log file for SQL");
        return Command::SUCCESS;

    }

    protected function generateSQLFixContentName(Content $content)
    {
        /*
         * insert into ezcontentobject_name(
            content_translation
            , content_version
            , contentobject_id
            , language_id
            , `name`
            , real_translation
            )
            values(
            'eng-US'
            ,	95
            ,	55514
            ,	2
            ,	'Friday, October 28, 2022'
            ,'eng-US')
         */

        $sql = sprintf("
 insert into %s (content_translation, content_version, contentobject_id, language_id, `name`, real_translation)
 values(':languageKey', :version, :contentId, :languageId, ':contentName', ':realTranslation');", $this->contentNameTable);

        $languageKey = $content->versionInfo->initialLanguageCode;
        $version = $content->versionInfo->versionNo;
        $contentId = $content->id;
        $languageId = $content->versionInfo->getInitialLanguage()->id;
        $contentName = empty($content->getName()) ? "new content" : $content->getName();
        $realTranslation = $content->versionInfo->initialLanguageCode;

        $sql = str_replace(':languageKey', $languageKey, $sql);
        $sql = str_replace(':version', $version, $sql);
        $sql = str_replace(':contentId', $contentId, $sql);
        $sql = str_replace(':languageId', $languageId, $sql);
        $sql = str_replace(':contentName', $contentName, $sql);
        $sql = str_replace(':realTranslation', $realTranslation, $sql);
        return $sql;

    }

    /**
     * Generate SQL for content name table where the version is not valid and should be removed
     * @param $contentId
     * @param $version
     * @return array|string|string[]
     */
    protected function generateSQLDeleteInvalidContentName($contentId, $version)
    {

        if (empty($contentId) || empty($version)) {
            return false;
        }
        $sql = sprintf("delete from %s where contentobject_id = :id and %s = :version;", $this->contentNameTable, $this->contentVersionVersionField);
        $sql = str_replace(':id', $contentId, $sql);
        $sql = str_replace(':version', $version, $sql);
        return $sql;
    }

    protected function queryContentListMissingName($offset, $limit)
    {
        // get set of contentIDs where missing from name table
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id');
        $qb->from($this->contentTable);
        $qb->where(sprintf('id not in (select contentobject_id from %s)', $this->contentNameTable));
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        $this->output->writeln($qb->getSQL());

        return $qb->execute()->fetchAllAssociative();
    }

    protected function queryContentNameMissingVersion($offset, $limit)
    {
        // get set of contentIDs and versions where ezcontentobject_name points to a missing content version
        $qb = $this->connection->createQueryBuilder();
        $qb->select("n.contentobject_id as id, n.content_version as version, concat('id_',n.contentobject_id,'-version_',n.content_version) as compound_key");
        $qb->from($this->contentNameTable, 'n');
        $qb->where(sprintf("concat('id_',n.contentobject_id,'-version_',n.content_version) not in ( select concat('id_',v.contentobject_id,'-version_',v.version) as compound_key from %s v )", $this->contentVersionTable));
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        $this->output->writeln($qb->getSQL());

        return $qb->execute()->fetchAllAssociative();
    }

    protected function queryContentMissingAnyVersion($offset, $limit) {
        // get content missing version records entirely

        $qb = $this->connection->createQueryBuilder();
        $qb->select("c.id");
        $qb->from($this->contentTable, 'c');
        $qb->where(sprintf("c.id not in (select contentobject_id from %s) ", $this->contentVersionTable));
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        $this->output->writeln($qb->getSQL());

        return $qb->execute()->fetchAllAssociative();
    }

    /**
     * @param $contentId
     * @return Content
     */
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
