<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteContentMissingLocationCommand extends Command
{

    protected Connection $connection;
    protected Repository $repository;
    protected ContentService $contentService;
    protected UserService $userService;
    protected PermissionResolver $permissionResolver;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;
    protected $save = false;
    protected $totalDeleted = 0;

    public const COMMAND_NAME = 'app:delete-content-missing-location';

    private $ibexaVersion = 5;
    private $contentTable = 'ibexa_content';
    private $contentTreeTable = 'ibexa_content_tree';
    private LocationService $locationService;

    /**
     * @param Connection $connection
     * @param Repository $repository
     * @param ContentService $contentService
     * @param LoggerInterface $validateContentLogger
     * @param UserService $userService
     * @param PermissionResolver $permissionResolver
     * @param LocationService $locationService
     * @throws DBALException
     */
    public function __construct(
        Connection         $connection,
        Repository         $repository,
        ContentService     $contentService,
        LoggerInterface    $validateContentLogger,
        UserService        $userService,
        PermissionResolver $permissionResolver, LocationService $locationService
    )
    {
        parent::__construct(self::COMMAND_NAME);
        $this->connection = $connection;
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->logger = $validateContentLogger;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
        $this->locationService = $locationService;
    }

    protected function ibexaVersionTableSwitcher()
    {
        if ($this->ibexaVersion < 5) {
            $this->contentTable = 'ezcontentobject';
            $this->contentTreeTable = 'ezcontentobject_tree';
        }
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Validate Content Tree')
            ->addOption(
                'content-id',
                null,
                InputOption::VALUE_REQUIRED,
                'specific Content ID; else query content by limit'
            )
            ->addOption(
                'content-status',
                null,
                InputOption::VALUE_REQUIRED,
                'specific content status 0/Draft; 1/Published; 2/Pending; 3/Archived; 4/Rejected; 5/Internal Draft; 6/Repeat; 7/Queued'
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
            ->addOption('remote-id-prefix', null, InputOption::VALUE_OPTIONAL, 'Remote ID prefix')
            ->addOption('delete-confirm', null, InputOption::VALUE_OPTIONAL, 'CONFIRM DELETION (CAREFUL) requires remote-id-prefix')
            ->addOption('ibexa-version', null, InputOption::VALUE_OPTIONAL, 'IBEXA version', 5);
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
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        // hackery for now, hope to read from kernel instead
        $this->ibexaVersion = $this->input->getOption('ibexa-version');
        $this->ibexaVersionTableSwitcher();

        $contentId = $input->getOption('content-id');
        $contentStatus = $input->getOption('content-status');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));
        $remoteIdPrefix = $input->getOption('remote-id-prefix');
        $deleteConfirm = $input->getOption('delete-confirm');

        $this->save = boolval($deleteConfirm);

        if ($this->save === true) {
            $this->runAsUser('admin');
        }

        $this->output->writeln('Running ..');
        $this->output->writeln('');

        if (!empty($contentId) && is_numeric($contentId)) {
            $contentIdRows = array(array('id' => $contentId));
        } else {
            $contentIdRows = $this->queryContentListByContentType($offset, $limit, $contentStatus, $remoteIdPrefix);
        }

        $count = 0 + $offset;

        foreach ($contentIdRows as $row) {
            $count++;
            $content = $this->getContentById($row['id']);
            $logPrefix = sprintf("contentID: [%s] %s (%s) [status %s] [%s] ", $content->id, $content->getName(), $content->getContentType()->identifier, $content->versionInfo->status, $content->contentInfo->remoteId);
            $output->writeln($logPrefix . " no location");
            $this->deleteContent($content, $logPrefix);
        }

        // Summary:
        $output->writeln(sprintf('Total: %s, processed: %s, limit: %s, offset: %s ', count($contentIdRows), $count, $offset, $limit));

        return Command::SUCCESS;

    }

    public function runAsUser($sUserLogin)
    {
        $user = $this->userService->loadUserByLogin($sUserLogin);
        $this->permissionResolver->setCurrentUserReference($user);
    }

    public function deleteContent(Content $content, $logPrefix)
    {
        // check truly no location
        $mainLanguageId = $content->contentInfo->mainLocationId;
        if (!empty($mainLanguageId) || $mainLanguageId === 0) {
            $this->output->write('<error>Found main location! skip</error> ');
            return;
        }

        if ($this->save === true) {
            // sudo delete
            $this->repository->sudo(
                function () use ($content) {
                    $this->contentService->deleteContent($content->contentInfo);
                }
            );

            $this->output->writeLn($logPrefix . "[Deleted]"); // linebreak
            $this->totalDeleted++;
        } else {
            $this->output->writeln($logPrefix . "Dry Run");
        }

    }

    protected function queryContentListByContentType($offset, $limit, $contentStatus = "", $remoteIdPrefix = "")
    {

        // get set of contentIDs
        $qb = $this->connection->createQueryBuilder();
        $qb->select('c.id');
        $qb->from($this->contentTable, 'c');
        $qb->leftJoin('c', $this->contentTreeTable, 't', 't.contentobject_id = c.id');
        $qb->where('1 = 1');
        if (!empty($contentStatus)) {
            $qb->andWhere('c.status = :status');
            $qb->setParameter('status', $contentStatus);
        }
        if (!empty($remoteIdPrefix)) {
            $qb->andWhere('c.remote_id like  "' . $remoteIdPrefix . "%" . '"'); // like as var
        }
        $qb->andWhere('t.node_id is null');
        // BAD $qb->andWhere(sprintf('id not in (select contentobject_id from %s)', $this->contentTreeTable));
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        echo $qb->getSQL() . "\n";
        if (count($qb->getParameters())) {
            echo print_r($qb->getParameters());
        }

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
