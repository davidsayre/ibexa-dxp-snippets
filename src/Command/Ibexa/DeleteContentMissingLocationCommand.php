<?php


namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
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

class DeleteContentMissingLocationCommand extends Command {

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

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection $connection,
        Repository $repository,
        ContentService $contentService,
        LoggerInterface $validateContentLogger,
        UserService $userService,
        PermissionResolver $permissionResolver
    )
    {
        parent::__construct(self::COMMAND_NAME);
        $this->connection = $connection;
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->logger = $validateContentLogger;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
    }

    protected function configure(): void
    {
        $this

            ->setDescription('Validate Content Tree')
            ->addOption(
                'content_id',
                'i',
                InputOption::VALUE_REQUIRED,
                'specific Content ID; else query content by limit'
            )
            ->addOption(
                'content-status',
                't',
                InputOption::VALUE_REQUIRED,
                'specific content status 0/Draft; 1/Published; 2/Pending; 3/Archived; 4/Rejected; 5/Internal Draft; 6/Repeat; 7/Queued'
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
            ->addOption('remote-id-prefix',null,InputOption::VALUE_OPTIONAL,'Remote ID prefix')
            ->addOption('delete-confirm',null,InputOption::VALUE_OPTIONAL,'CONFIRM DELETION (CAREFUL) requires remote-id-prefix')
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
        $contentStatus = $input->getOption('content-status');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));
        $remoteIdPrefix = $input->getOption('remote-id-prefix');
        $deleteConfirm = $input->getOption('delete-confirm');

        $this->save = boolval($deleteConfirm);

        if($this->save === true) {
            $this->runAsUser('admin');
        }

        $this->output->writeln('Running ..');
        $this->output->writeln('');

        if(!empty($contentId) && is_numeric($contentId)) {
            $contentIdRows = array(array('id'=>$contentId));
        } else {
            $contentIdRows = $this->queryContentListByContentType($offset, $limit, $contentStatus, $remoteIdPrefix);
        }

        $count = 0 + $offset;

        foreach($contentIdRows as $row) {
            $count++;
            $content = $this->getContentById($row['id']);
            $logPrefix = "contentID: [".$content->id."] '".$content->getName()."' (".$content->getContentType()->identifier.") [status ".$content->versionInfo->status."] [".$content->contentInfo->remoteId."] ";
            $output->writeln( $logPrefix." no location");
            if(!empty($remoteIdPrefix) && $deleteConfirm === "1") {
                $this->deleteContent($content, $logPrefix);
            }
        }

        // Summary:
        echo "Query: count: ".$count." offset: ".$offset. " limit: ".$limit."\n";

        return Command::SUCCESS;

    }

    public function runAsUser($sUserLogin) {
        $user = $this->userService->loadUserByLogin($sUserLogin);
        $this->permissionResolver->setCurrentUserReference($user);
    }

    public function deleteContent(Content $content,$logPrefix) {
        if($this->save === true) {
            $this->contentService->deleteContent($content->contentInfo);
            $this->output->writeLn($logPrefix."[Deleted]"); // linebreak
            $this->totalDeleted++;
        } else {
            $this->output->writeln($logPrefix."Dry Run");
        }

    }

    protected function queryContentListByContentType($offset, $limit, $contentStatus = "", $remoteIdPrefix = ""){
        /*
         * select * from ezcontentobject where id not in (select contentobject_id from ezcontentobject_name)
         */
        // get set of contentIDs
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id');
        $qb->from('ezcontentobject');
        $qb->where('1 = 1');
        if(!empty($contentStatus)){
            $qb->andWhere('status = :status');
            $qb->setParameter('status',$contentStatus);
        }
        if(!empty($remoteIdPrefix)){
            $qb->andWhere('remote_id like  "'.$remoteIdPrefix."%".'"'); // like as var
        }
        $qb->andWhere('id not in (select contentobject_id from ezcontentobject_tree)');
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        echo $qb->getSQL()."\n";
        echo print_r($qb->getParameters());

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