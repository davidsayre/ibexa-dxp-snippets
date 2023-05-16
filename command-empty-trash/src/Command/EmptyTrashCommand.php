<?php

/**
 * Author @ David Sayre
 * Repo: https://github.com/davidsayre/ibexa-dxp-snippets
 */

declare(strict_types=1);

namespace App\Command;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Trash\SearchResult;
use Ibexa\Core\Repository\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EmptyTrashCommand extends Command
{
    private $repository;

    public const COMMAND_NAME = 'app:empty-trash';

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        parent::__construct("name");
    }

    protected function configure(): void
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Empty trash')
            ->addOption('limit', '--limit', InputOption::VALUE_OPTIONAL, 'number of items to process: default 10', 10)
            ->addOption('save', '--save', InputOption::VALUE_OPTIONAL, '--save 1 will make real DB and File changes; else dry run logging', 0);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        // save / dry run
        $save = false;
        if ($input->getOption('save') == "1" | $input->getOption('save') == true) {
            $save = true;
        }
        $output->writeln('Running ..');
        $output->writeln('');

        $query = new Query();
        $query->limit = $limit;

        /** @var SearchResult $trashItems */
        $trashItems = $this->repository->sudo(
            function () use ($query) {
                return $this->repository->getTrashService()->findTrashItems($query);
            }
        );
        $totalProcessed = 0;
        $totalDeleted = 0;
        $totalDryRun = 0;
        foreach ($trashItems->items as $item) {
            $totalProcessed++;
            $content = $item->getContent();
            $output->write( "content [".$content->id."] ".$content->getName()." "); // no line break, space
            if($save === true) {
                try {
                    $this->repository->sudo(
                        function () use ($item) {
                            return $this->repository->getTrashService()->deleteTrashItem($item);
                        }
                    );
                    $totalDeleted++;
                    $output->writeln("[removed]"); // line break
                } catch (\Exception $e) {
                    $output->writeln("ERROR .. "); // line break
                    echo "File: ".$e->getFile()."\n";
                    echo "Line: ".$e->getLine()."\n";
                    echo "Message: ".$e->getMessage()."\n";
                }
            } else {
                $totalDryRun++;
                $output->writeln("[Dry Run]"); //line break
            }
        }
        $output->writeln("Total processed: ".$totalProcessed);
        $output->writeln("Total deleted: ".$totalDeleted);
        $output->writeln("Total dry run: ".$totalDryRun);

        return Command::SUCCESS;

    }

}