<?php

namespace App\Command\Ibexa;
/**
 * Author: David Sayre / Allegiance Group
 */

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\URL\Query\Criterion\MatchAll;
use Ibexa\Contracts\Core\Repository\Values\URL\URL;
use Ibexa\Contracts\Core\Repository\Values\URL\URLQuery;
use Ibexa\Core\Repository\Repository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * example: bin/console app:unused-urls:delete --save=1 --limit=10000 | grep "DELETE" > var/delete_urls.sql
 */
class UnusedURLDeleteCommand extends Command {

    public const COMMAND_NAME = "app:unused-urls:delete";
    private $urlService;
    private $userService;
    private $permissionResolver;
    protected $logger;
    protected Connection $connection;
    protected Repository $repository;

    protected InputInterface $input;
    protected OutputInterface $output;

    public function __construct(
        URLService $urlService,
        UserService $userService,
        PermissionResolver $permissionResolver,
        LoggerInterface $validateContentLogger,
        Connection $connection,
        Repository $repository
    ) {
        $this->urlService = $urlService;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
        $this->logger = $validateContentLogger;
        $this->connection = $connection;
        $this->repository = $repository;

        parent::__construct(self::COMMAND_NAME);
    }

    protected function configure(): void
    {
        $this->setDescription("Delete unused URLs")
            ->addOption('offset', null, InputOption::VALUE_OPTIONAL, 'Query offset', 0)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'query batch limit: default 20 (note query != actual processed)', 20)
            ->addOption('save', null, InputOption::VALUE_OPTIONAL, '--save 1 will make real DB and File changes; else dry run logging', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->permissionResolver->setCurrentUserReference($this->userService->loadUser(14));

        $query = new URLQuery();
        $query->filter = new MatchAll();
        $query->limit = intval($input->getOption('limit'));
        $query->offset = intval($input->getOption('offset'));

        /** @var URL $r */
        foreach ($this->urlService->findUrls($query) as $r) {
            $message = "id: $r->id and url: $r->url";
            $output->write($message);
            if ($this->urlService->findUsages($r)->totalCount === 0) {
                $output->writeln(" [0 use]");
                if(boolval($input->getOption('save'))) {

                    // Delete url
                    $qb = $this->connection->createQueryBuilder();
                    $qb->delete('ezcontentobject_link','ecl');
                    $qb->from('ecl');
                    $qb->where("id = ".$r->id);

                    // for now, render as SQL to be copied into text and run on DB connection after backup
                    echo $qb->getSQL()."\n";

                    // log deletion
                    // $this->logger->info("Deleting unused Url ".$message);
                }
            } else {
               $output->writeln(" [IN USE]");
            }
        }

        return Command::SUCCESS;
    }
}
?>