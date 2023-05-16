<?php

/**
 * Author @ David Sayre
 * Repo: https://github.com/davidsayre/ibexa-dxp-snippets
 */

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Ibexa\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CanDeleteLanguageCommand extends Command
{

    /**
     * The native Doctrine connection.
     *
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    public const COMMAND_NAME = 'app:can-delete-language';

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(Connection $connection)
    {
        parent::__construct("name");
        $this->connection = $connection;
        $this->dbPlatform = $this->connection->getDatabasePlatform();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Can delete language')
            ->addOption(
                'language_id',
                'l',
                InputOption::VALUE_REQUIRED,
                'Language_id'
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $languageId = $input->getOption('language_id');

        if (!is_numeric($languageId)) {
            throw new InvalidArgumentException('language_id value has to be an integer.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $languageId = (int)$input->getOption('language_id');

        $output->writeln('Running ..');
        $output->writeln('');

        // note: at some point this should be delegated to specific gateways
        $tableErrors = array();
        foreach (DoctrineDatabase::MULTILINGUAL_TABLES_COLUMNS as $tableName => $columns) {
            $languageMaskColumn = $columns[0];
            $languageIdColumn = $columns[1] ?? null;
            if (
                $this->countTableData($languageId, $tableName, $languageMaskColumn, $languageIdColumn) > 0
            ) {
                $tableErrors[] = $tableName;
            }
        }

        foreach ($tableErrors as $tableError) {
            echo "language in use : ".$tableError."\n";
        }


        return Command::SUCCESS;

    }

    /**
     * Count table data rows related to the given language.
     *
     * @param string|null $languageIdColumn optional column name containing explicit language id
     */
    private function countTableData(
        int $languageId,
        string $tableName,
        string $languageMaskColumn,
        ?string $languageIdColumn = null
    ): int {
        $query = $this->connection->createQueryBuilder();
        $query
            // avoiding using "*" as count argument, but don't specify column name because it varies
            ->select($this->dbPlatform->getCountExpression(1))
            ->from($tableName)
            ->where(
                $query->expr()->gt(
                    $this->dbPlatform->getBitAndComparisonExpression(
                        $languageMaskColumn,
                        $query->createPositionalParameter($languageId, ParameterType::INTEGER)
                    ),
                    0
                )
            );
        if (null !== $languageIdColumn) {
            $query
                ->orWhere(
                    $query->expr()->eq(
                        $languageIdColumn,
                        $query->createPositionalParameter($languageId, ParameterType::INTEGER)
                    )
                );
        }

        return (int)$query->execute()->fetchColumn();
    }

}

