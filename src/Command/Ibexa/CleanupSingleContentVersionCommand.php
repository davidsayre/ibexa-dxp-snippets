<?php

/**
 * Author: David Sayre / Allegiance Group
 * Modified variation of the Ibexa Command but for a single content object
 */

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;
use Ibexa\Bundle\Core\Command\CleanupVersionsCommand;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class CleanupSingleContentVersionCommand extends Command {

    /** @var \Ibexa\Contracts\Core\Repository\Repository */
    private $repository;

    /** @var \Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    public const COMMAND_NAME = 'app:content:cleanup-single-version';

    public function __construct(
        Repository $repository,
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        Connection $connection
    ) {
        $this->repository = $repository;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;

        parent::__construct(self::COMMAND_NAME);
    }
    
    protected function configure()
    {
        $beforeRunningHints = CleanupVersionsCommand::BEFORE_RUNNING_HINTS;
        $this
            ->setDescription('Removes unwanted content versions. Keeps the published version untouched. By default, also keeps the last archived/draft version.')
            ->addOption(
                'status',
                't',
                InputOption::VALUE_OPTIONAL,
                sprintf(
                    "Select which version types should be removed: '%s', '%s', '%s'.",
                    CleanupVersionsCommand::VERSION_DRAFT,
                    CleanupVersionsCommand::VERSION_ARCHIVED,
                    CleanupVersionsCommand::VERSION_ALL
                ),
                CleanupVersionsCommand::VERSION_ALL
            )
            ->addOption(
                'keep',
                'k',
                InputOption::VALUE_OPTIONAL,
                "Sets the number of the most recent versions (both drafts and archived) which won't be removed.",
                'config_default'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Ibexa username (with Role containing at least content policies: remove, read, versionread)',
                CleanupVersionsCommand::DEFAULT_REPOSITORY_USER
            )
            ->addOption(
                'content-id',
                null,
                InputOption::VALUE_REQUIRED,
                'SINGLE content-id whose versions should be removed.',
                null
            )->setHelp(
                <<<EOT
The command <info>%command.name%</info> reduces content versions to a minimum. 
It keeps published version untouched, and by default also keeps the last archived/draft version.
This command is modified to only run against a single content-id. Example to delete status (t) draft and (k)eep 0:  --content-id=59117 -t draft -k 0

{$beforeRunningHints}
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // We don't load repo services or config resolver before execute() to avoid loading before SiteAccess is set.
        $keep = $input->getOption('keep');
        if ($keep === 'config_default') {
            $config = $this->repositoryConfigurationProvider->getRepositoryConfig();
            $keep = $config['options']['default_version_archive_limit'];
        }

        if (($keep = (int) $keep) < 0) {
            throw new InvalidArgumentException(
                'keep',
                'Keep value cannot be negative.'
            );
        }

        $userService = $this->repository->getUserService();
        $contentService = $this->repository->getContentService();
        $permissionResolver = $this->repository->getPermissionResolver();

        $permissionResolver->setCurrentUserReference(
            $userService->loadUserByLogin($input->getOption('user'))
        );

        $status = $input->getOption('status');

        $contentId = $input->getOption('content-id');
        $contentIds = [];
        $contentIds[] = $contentId;
        $contentIdsCount = count($contentIds);

        if ($contentIdsCount === 0) {
            $output->writeln('<info>Missing content-id.</info>');
            return Command::INVALID;
        }

        $output->writeln(sprintf(
            '<info>Found %d Content IDs matching the given Criteria.</info>',
            $contentIdsCount
        ));

        $displayProgressBar = !($output->isVerbose() || $output->isVeryVerbose() || $output->isDebug());

        if ($displayProgressBar) {
            $progressBar = new ProgressBar($output, $contentIdsCount);
            $progressBar->setFormat(
                '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%' . PHP_EOL
            );
            $progressBar->start();
        }

        $removedVersionsCounter = 0;

        $removeAll = $status === CleanupVersionsCommand::VERSION_ALL;

        foreach ($contentIds as $contentId) {
            try {
                $contentInfo = $contentService->loadContentInfo((int) $contentId);
                $versions = $contentService->loadVersions(
                    $contentInfo,
                    $removeAll ? null : $this->mapStatusToVersionInfoStatus($status)
                );
                $versionsCount = count($versions);

                $output->writeln(sprintf(
                    '<info>Content %d has %d version(s)</info>',
                    (int) $contentId,
                    $versionsCount
                ), OutputInterface::VERBOSITY_VERBOSE);

                if ($removeAll) {
                    $versions = array_filter($versions, static function (VersionInfo $version) {
                        return $version->status !== VersionInfo::STATUS_PUBLISHED;
                    });
                }

                if ($keep > 0) {
                    $versions = array_slice($versions, 0, -$keep);
                }

                $output->writeln(sprintf(
                    'Found %d content (%d) version(s) to remove.',
                    count($versions),
                    (int) $contentId
                ), OutputInterface::VERBOSITY_VERBOSE);

                /** @var \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $version */
                foreach ($versions as $version) {
                    $contentService->deleteVersion($version);
                    ++$removedVersionsCounter;
                    $output->writeln(sprintf(
                        'Content (%d) version (%d) has been deleted.',
                        $contentInfo->id,
                        $version->id
                    ), OutputInterface::VERBOSITY_VERBOSE);
                }

                if ($displayProgressBar) {
                    $progressBar->advance(1);
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf(
                    '<error>%s</error>',
                    $e->getMessage()
                ));
            }
        }

        $output->writeln(sprintf(
            '<info>Removed %d unwanted contents version(s) from %d Content item(s).</info>',
            $removedVersionsCounter,
            $contentIdsCount
        ));

        return Command::SUCCESS;
    }

    /**
     * @param string $status
     *
     * @return int
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     */
    private function mapStatusToVersionInfoStatus($status)
    {
        if (array_key_exists($status, CleanupVersionsCommand::VERSION_STATUS)) {
            return CleanupVersionsCommand::VERSION_STATUS[$status];
        }

        throw new InvalidArgumentException(
            'status',
            sprintf(
                'Status %s cannot be mapped to a VersionInfo status.',
                $status
            )
        );
    }
}

?>