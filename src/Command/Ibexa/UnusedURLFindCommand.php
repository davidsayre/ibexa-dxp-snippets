<?php

/**
 * Author: David Sayre / Allegiance Group
 */

declare(strict_types=1);

namespace App\Command\Ibexa;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\URL\Query\Criterion\MatchAll;
use Ibexa\Contracts\Core\Repository\Values\URL\URLQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnusedURLFindCommand extends Command
{
    public const COMMAND_NAME = "app:unused-urls:find";
    private $urlService;
    private $userService;
    private $permissionResolver;

    public function __construct(
        URLService $urlService,
        UserService $userService,
        PermissionResolver $permissionResolver
    ) {
        $this->urlService = $urlService;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
        parent::__construct(self::COMMAND_NAME);
    }

    protected function configure(): void
    {
        $this->setDescription("Find unused URLs");
    }

    // @phan-suppress-next-line PhanUnusedProtectedMethodParameter
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->permissionResolver->setCurrentUserReference($this->userService->loadUser(14));

        $query = new URLQuery();
        $query->filter = new MatchAll();
        $query->limit = \PHP_INT_MAX;

        foreach ($this->urlService->findUrls($query) as $r) {
            if ($this->urlService->findUsages($r)->totalCount === 0) {
                $output->writeln("id: $r->id and url: $r->url");
            }
        }

        return Command::SUCCESS;
    }
}