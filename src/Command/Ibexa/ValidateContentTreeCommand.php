<?php


namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateContentTreeCommand extends Command {

    protected Connection $connection;
    protected Repository $repository;
    protected ContentService $contentService;
    protected LocationService $locationService;
    protected UserService $userService;
    protected PermissionResolver $permissionResolver;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;
    protected $save = false;
    protected $totalDeleted = 0;

    public const COMMAND_NAME = 'app:validate-content:tree';

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection $connection,
        Repository $repository,
        ContentService $contentService,
        LocationService $locationService,
        LoggerInterface $validateContentLogger,
        UserService $userService,
        PermissionResolver $permissionResolver
    )
    {
        parent::__construct(self::COMMAND_NAME);
        $this->connection = $connection;
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->logger = $validateContentLogger;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
    }

    protected function configure(): void
    {
        $this
            
            ->setDescription('Validate Content Tree')
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

        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));

       $this->runAsUser('admin');

        $this->output->writeln('Running ..');
        $this->output->writeln('');

        $locationIdRows = $this->queryInvalidMainLocations($offset, $limit);

        $count = 0 + $offset;

        foreach($locationIdRows as $row) {
            $count++;
            $mainNodeId = $row['main_node_id'];
            $nodeId = $row['node_id'];
            $contentId = $row['content_id'];
            $content = $this->getContentById($contentId);
            //$location = $this->getLocationById($locationId);
            $logPrefix = "contentID: ["
                .$content->id."] '"
                .$content->getName()
                ."' (".$content->getContentType()->identifier
                .") [status "
                .$content->versionInfo->status."] "
                ."mainLocationID: [".$mainNodeId."] "
                ."nodeId: [".$nodeId."]"
            ;
            $output->writeln( $logPrefix);
        }

        $output->writeln("Check above. There should be at least 1 row with a matching main_node_id = node_id");

        // Summary:
        echo "Query: count: ".$count." offset: ".$offset. " limit: ".$limit."\n";

        return Command::SUCCESS;

    }

    public function runAsUser($sUserLogin) {
        $user = $this->userService->loadUserByLogin($sUserLogin);
        $this->permissionResolver->setCurrentUserReference($user);
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

    /**
     * @param $locationId
     * @return Location
     */
    protected function getLocationById($locationId) {
        return $this->repository->sudo(
            function () use ($locationId) {
                return $this->locationService->loadLocation($locationId);
            }
        );
    }

    protected function queryInvalidMainLocations($offset, $limit) {
        $qb = $this->connection->createQueryBuilder();
        $qb->addSelect('contentobject_id as content_id, main_node_id, node_id');
        $qb->from('ezcontentobject_tree');
        $qb->where('contentobject_id in (select contentobject_id from ezcontentobject_tree where main_node_id != node_id)');
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $qb->orderBy('contentobject_id');

        echo $qb->getSQL()."\n";

        return $qb->execute()->fetchAllAssociative();
    }

}

?>