<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

class DeleteUntouchedDraftsCommand extends Command
{

    const MIN_PUBLISHED_DAYS_OLD = 30;

    protected Connection $connection;
    protected $contentService;
    protected $repository;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;

    public const COMMAND_NAME = 'app:delete-untouched-drafts';

    private $ibexaVersion = 5;
    private $contentTable = 'ibexa_content';
    private $contentTreeTable = 'ibexa_content_tree';

    /**
     * @throws DBALException
     */
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

    protected function ibexaVersionTableSwitcher()
    {
        if ($this->ibexaVersion === 4) {
            $this->contentTable = 'ezcontentobject';
            $this->contentTreeTable = 'ezcontentobject_tree';
        }
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete untouched draft(s)')
            ->addOption('content-id', null, InputOption::VALUE_REQUIRED, 'specific Content ID; else query content by limit')
            ->addOption('offset', null, InputOption::VALUE_OPTIONAL, 'Query offset', 0)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'number of items to process: default 10', 10)
            ->addOption('save', null, InputOption::VALUE_OPTIONAL, '--save 1 will make real DB and File changes; else dry run logging', 0)
            ->addOption('ibexa-version', null, InputOption::VALUE_OPTIONAL, 'IBEXA version', 5);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $contentId = $input->getOption('content-id');
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

        $contentId = $input->getOption('content-id');
        $offset = (int)$input->getOption('offset');
        $limit = (int)$input->getOption('limit');
        // save / dry run
        $save = false;
        if ($input->getOption('save') == "1" | $input->getOption('save') == true) {
            $save = true;
        }

        $this->output->writeln('Running ..');
        $this->output->writeln('');

        $query = new Query();
        $query->limit = $limit;

        if (!empty($contentId) && is_numeric($contentId)) {
            $contentIdRows = array(array('id' => $contentId));
        } else {
            $contentIdRows = $this->queryUntouchedDrafts($offset, $limit);
        }

        $totalProcessed = 0;
        $totalDeleted = 0;
        $totalDryRun = 0;
        foreach ($contentIdRows as $row) {
            $totalProcessed++;
            $contentId = $row['id'];
            $contentDraftName = $row['name'];
            $published = $row['published'];
            /** @var Content $content */
            $content = $this->repository->sudo(
                function () use ($contentId) {
                    return $this->contentService->loadContent($contentId);
                }
            );
            $contentName = $content->getName();
            if (empty($contentName)) {
                $contentName = "(draft) " . $contentDraftName;
            }
            $this->output->write("content [" . $content->id . "] name: '" . $contentName . "' "); // no line break, space

            // PRE CHECK (MUST BE 100%!!)
            if ($content->getVersionInfo()->versionNo !== 1) {
                $this->output->writeln("ERROR: content version != 1");
                continue;
            }
            if ($content->getVersionInfo()->status !== 0) {
                $this->output->writeln(sprintf("ERROR: content status [%s] != 0",$content->getVersionInfo()->status));
                continue;
            }
            if ($content->getVersionInfo()->isPublished() === true) {
                $this->output->writeln("ERROR: content is published");
                continue;
            }
            if ($content->getVersionInfo()->isDraft() === false) {
                $this->output->writeln("ERROR: content is not draft");
                continue;
            }
            // check created > 7 days ago (HARD FLOOR) or whatever MIN set above
            $createdDiffInterval = date_diff($content->getVersionInfo()->creationDate, new DateTime());
            if ($createdDiffInterval->days < 7 || $createdDiffInterval->days <= self::MIN_PUBLISHED_DAYS_OLD) {
                $this->output->writeln("ERROR: content only " . $createdDiffInterval->days . " days old. min(>7 | >" . self::MIN_PUBLISHED_DAYS_OLD . ") ");
                continue;
            }

            // should not have child items becuase version = 1
            if (!empty($content->contentInfo->mainLocationId)) {
                $this->output->writeln("ERROR: content has location");
                continue;
            }

            if ($save === true) {
                try {
                    $this->repository->sudo(
                        function () use ($content) {
                            $this->contentService->deleteContent($content->contentInfo);
                        }
                    );
                    $totalDeleted++;
                    $this->output->writeln("[removed]"); // line break
                } catch (Exception $e) {
                    $this->output->writeln("ERROR .. "); // line break
                    echo "File: " . $e->getFile() . "\n";
                    echo "Line: " . $e->getLine() . "\n";
                    echo "Message: " . $e->getMessage() . "\n";
                }
            } else {
                $totalDryRun++;
                $this->output->writeln("[Dry Run]"); //line break
            }
        }
        $this->output->writeln("Total processed: " . $totalProcessed);
        $this->output->writeln("Total deleted: " . $totalDeleted);
        $this->output->writeln("Total dry run: " . $totalDryRun);

        return Command::SUCCESS;

    }

    protected function queryUntouchedDrafts($offset, $limit)
    {
        // get set of contentIDs
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id,name,status,published,current_version');
        $qb->from($this->contentTable);
        $qb->where('status = 0');
        $qb->andWhere('published = 0');
        $qb->andWhere('current_version = 1'); // MAY change this later
        $qb->orderBy('id');
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        return $qb->execute()->fetchAllAssociative();
    }
}

?>
