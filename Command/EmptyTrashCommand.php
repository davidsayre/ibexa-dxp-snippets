<?php

declare(strict_types=1);

namespace App\Command;

use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use Ibexa\AdminUi\Form\Data\Trash\TrashEmptyData;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Core\Repository\Repository;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Trash\SearchResult;


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
            ->addOption(
                'limit',
                'n',
                InputOption::VALUE_OPTIONAL,
                'item limit',
                100
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $timeout = $input->getOption('timeout');

        if (!is_numeric($timeout)) {
            throw new InvalidArgumentException('Timeout value has to be an integer.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

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
        foreach ($trashItems->items as $item) {
            $content = $item->getContent();
            echo "".$item->id." ".$content->getName()."\n";
            $this->repository->sudo(
                function () use ($item) {
                    return $this->repository->getTrashService()->deleteTrashItem($item);
                }
            );
        }

        return Command::SUCCESS;

    }

}
