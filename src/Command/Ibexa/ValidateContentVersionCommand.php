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

class ValidateContentVersionCommand extends Command {

    protected Connection $connection;
    protected $contentService;
    protected $repository;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;

    public const COMMAND_NAME = 'app:validate-content:versions';

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
        ;
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
        if(!empty($contentId) && is_numeric($contentId)) {
            $contentIdRows = array(array('id'=>$contentId));
        } else {
            $contentIdRows = $this->queryContentListMissingName($offset, $limit);
        }

        $count = 0 + $offset;
        foreach($contentIdRows as $row) {
            $count++;
            $content = $this->getContentById($row['id']);
            $this->logger->error("contentID: [".$content->id."] ".$content->getName()." missing ezcontentobject_name record");
            $sql = "/* SQL */".$this->generateSQLFixContentName($content);
            $output->writeln($sql);
            $this->logger->info($sql);
        }

        // part 2: check for invalid content names
        if(!empty($contentId) && is_numeric($contentId)) {
            $contentRows = array(array('id'=>$contentId));
        } else {
            $contentRows = $this->queryContentNameMissingVersion($offset, $limit);
        }

        $count = 0 + $offset;
        foreach($contentRows as $row) {
            $count++;
            $this->logger->error("contentID: [".$row['id']."] version [".$row['version']."] name is missing ezcontentobject_version match");
            $sql = "/* SQL */ ".$this->generateSQLDeleteInvalidContentName($row['id'],$row['version'] );
            $output->writeln($sql);
            $this->logger->info($this->generateSQLDeleteInvalidContentName($row['id'],$row['version'] ));
        }

        // Summary:
        echo "Query: count: ".$count." offset: ".$offset. " limit: ".$limit."\n";
        $output->writeln(">> Check the .log file for SQL");
        return Command::SUCCESS;

    }

    protected function generateSQLFixContentName(Content $content) {
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

        $sql = "
 insert into ezcontentobject_name(content_translation, content_version, contentobject_id, language_id, `name`, real_translation)
 values(':languageKey', :version, :contentId, :languageId, ':contentName', ':realTranslation');";

        $languageKey = $content->versionInfo->initialLanguageCode;
        $version = $content->versionInfo->versionNo;
        $contentId = $content->id;
        $languageId = $content->versionInfo->getInitialLanguage()->id;
        $contentName = empty($content->getName()) ? "new content" :  $content->getName();
        $realTranslation = $content->versionInfo->initialLanguageCode;

        $sql = str_replace(':languageKey',$languageKey, $sql);
        $sql = str_replace(':version',$version, $sql);
        $sql = str_replace(':contentId',$contentId, $sql);
        $sql = str_replace(':languageId',$languageId, $sql);
        $sql = str_replace(':contentName',$contentName, $sql);
        $sql = str_replace(':realTranslation',$realTranslation, $sql);
        return $sql;

    }

    /**
     * Generate SQL for ezcontentobject_name table where the version is not valid and shold be removed
     * @param $contentId
     * @param $version
     * @return array|string|string[]
     */
    protected function generateSQLDeleteInvalidContentName($contentId, $version) {

        if(empty($contentId) || empty($version)) {
            return false;
        }
        $sql = "delete from ezcontentobject_name where contentobject_id = :id and version = :version";
        $sql = str_replace(':id',$contentId, $sql);
        $sql = str_replace(':version',$version, $sql);
        return $sql;
    }

    protected function queryContentListMissingName($offset, $limit){
        // get set of contentIDs where missing from ezcontentobject_name
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id');
        $qb->from('ezcontentobject');
        $qb->where('id not in (select contentobject_id from ezcontentobject_name)');
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->execute()->fetchAllAssociative();
    }

    protected function queryContentNameMissingVersion($offset, $limit){
        // get set of contentIDs and versions where ezcontentobject_name points to a missing content version
        /*
            select econ.contentobject_id, econ.content_version, concat('id_',econ.contentobject_id,'-version_',econ.content_version) as compound_key
            from ezcontentobject_name econ
            where concat('id_',econ.contentobject_id,'-version_',econ.content_version) not in
            ( select concat('id_',ecov.contentobject_id,'-version_',ecov.version) as compound_key from ezcontentobject_version ecov )
         */
        $qb = $this->connection->createQueryBuilder();
        $qb->select("econ.contentobject_id as id, econ.content_version as version, concat('id_',econ.contentobject_id,'-version_',econ.content_version) as compound_key");
        $qb->from('ezcontentobject_name','econ');
        $qb->where("concat('id_',econ.contentobject_id,'-version_',econ.content_version) not in ( select concat('id_',ecov.contentobject_id,'-version_',ecov.version) as compound_key from ezcontentobject_version ecov )");
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->execute()->fetchAllAssociative();
    }

    /**
     * @param $contentId
     * @return Content
     */
    protected function getContentById($contentId) {
        return $this->repository->sudo(
            function () use ($contentId) {
                return $this->contentService->loadContent($contentId);
            }
        );
    }

}

?>