<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use App\Service\Admin\Reports\ReportContentImageToArchiveService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReportContentImageToArchiveCommand extends Command
{
    private ReportContentImageToArchiveService $reportContentImageToArchiveService;

    protected InputInterface $input;
    protected OutputInterface $output;

    protected static $defaultName = 'app:image-to-archive-report:generate';

    public function __construct( ReportContentImageToArchiveService $reportContentImageToArchiveService  )
    {
        parent::__construct();
        $this->reportContentImageToArchiveService = $reportContentImageToArchiveService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate Image to Archive Report')
            ->addOption(
                'archive-sections',
                null,
                InputOption::VALUE_REQUIRED,
                'Archive section identifiers (ex: "archive,super_admin_only")',
            )
            ->addOption(
                'content-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'single content-id vs query',
                ''
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_OPTIONAL,
                'Username (admin)',
                ''
            )
            ->addOption(
                'offset',
                null,
                InputOption::VALUE_OPTIONAL,
                'Query offset',
                0
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Query limit',
                1
            )
            ->addOption(
                'save',
                null,
                InputOption::VALUE_OPTIONAL,
                'Save to database',
                0
            )
            ->addOption(
                'truncate-table',
                null,
                InputOption::VALUE_OPTIONAL,
                'delete all records and start over',
                false
            )
            ->addOption(
                'log-level',
                null,
                InputOption::VALUE_OPTIONAL,
                'Log level',
                3
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $contentId = $input->getOption('content-id');
        $username = $input->getOption('username');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));
        $save = boolval($input->getOption('save'));
        $truncateTable = $input->getOption('truncate-table');
        $logLevel = intval($input->getOption('log-level'));
        $archiveSections = $input->getOption('archive-sections');

        // Part 1: parse content
        $this->reportContentImageToArchiveService->setOutput($output);
        $this->reportContentImageToArchiveService->setSave($save);
        $this->reportContentImageToArchiveService->loginUserOnProcess($username);
        $this->reportContentImageToArchiveService->setLogLevel($logLevel);
        if($truncateTable) {
            $this->reportContentImageToArchiveService->setTruncateTable(true);
            $this->reportContentImageToArchiveService->purgeAllReportItems();
        }
        if(!empty($archiveSections)) {
            $this->reportContentImageToArchiveService->setArchiveSections(explode(",", $archiveSections));
        } else {
            $output->writeln("Error: No --archive-sections specified");
            return Command::FAILURE;
        }
        if(!empty($contentId)) {
            $reportResults = $this->reportContentImageToArchiveService->generateReportItemFromOneContent($contentId);
        } else {
            $reportResults = $this->reportContentImageToArchiveService->generateReportItems($limit, $offset);
        }

        // print_r($contentReportItems);

        // part 2: per content / per field / report items
        $this->reportContentImageToArchiveService->storeReportResults($reportResults);
        return Command::SUCCESS;

    }
}