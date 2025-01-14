<?php

/**
 * Author: David Sayre / Allegiance Group
 */


use App\Service\Admin\Reports\ReportContentRichTextImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RichTextImageReportGenerateCommand extends Command
{

    private ReportContentRichTextImageService $reportContentRichTextImageService;

    protected InputInterface $input;
    protected OutputInterface $output;

    public const COMMAND_NAME = 'app:richtext-image-report:generate';

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct( ReportContentRichTextImageService $reportContentRichTextImageService )
    {
        parent::__construct(self::COMMAND_NAME);
        $this->reportContentRichTextImageService = $reportContentRichTextImageService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate Report RichText Images')
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
        if($truncateTable) {
            $this->reportContentRichTextImageService->setTruncateTable(true);
            $this->reportContentRichTextImageService->purgeAllReportItems();
        }
        if(!empty($contentId)) {
            $results = $this->reportContentRichTextImageService->generateReportItemFromOneContent($contentId);
        } else {
            $results = $this->reportContentRichTextImageService->generateReportItems($limit, $offset);
        }

        // print_r($contentReportItems);

        // part 2: per content / per field / report items
        $this->reportContentRichTextImageService->storeReportResults($results);
        return Command::SUCCESS;

    }

}

?>