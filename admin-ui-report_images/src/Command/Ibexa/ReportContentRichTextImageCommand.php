<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use App\Service\Admin\Reports\ReportContentRichTextImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReportContentRichTextImageCommand extends Command
{

    private ReportContentRichTextImageService $reportContentRichTextImageService;

    protected InputInterface $input;
    protected OutputInterface $output;

    protected static $defaultName = 'app:richtext-image-report:generate';

    public function __construct( ReportContentRichTextImageService $reportContentRichTextImageService  )
    {
        parent::__construct();
        $this->reportContentRichTextImageService = $reportContentRichTextImageService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate RichText Images Report')
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

        // Part 1: parse content
        $this->reportContentRichTextImageService->setOutput($output);
        $this->reportContentRichTextImageService->setSave($save);
        $this->reportContentRichTextImageService->loginUserOnProcess($username);
        $this->reportContentRichTextImageService->setLogLevel($logLevel);

        // truncate-table option
        if($truncateTable) {
            $this->reportContentRichTextImageService->setTruncateTable(true);
            $this->reportContentRichTextImageService->purgeAllReportItems();
            return Command::SUCCESS;
        }

        // proceed with processing
        if(!empty($contentId)) {
            $results = $this->reportContentRichTextImageService->generateReportItemFromOneContent($contentId);
        } else {
            $results = $this->reportContentRichTextImageService->generateReportItems($limit, $offset);
        }

        // part 2: store parsed results in database
        $this->reportContentRichTextImageService->storeReportResults($results);
        return Command::SUCCESS;

    }

}

?>