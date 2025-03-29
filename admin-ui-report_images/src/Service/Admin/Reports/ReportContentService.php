<?php

namespace App\Service\Admin\Reports;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate report items
 */
class ReportContentService
{

    protected EntityManagerInterface $em;
    protected Connection $connection;
    protected ContentService $contentService;
    protected UserService $userService;
    protected PermissionResolver $permissionResolver;
    protected OutputInterface $output;

    protected $logLevel = 3; // 1 = process; 2 = sub process; 3 = content/item; 4 = field; 5 = values;
    protected $save = false;

    protected $tableName = ''; // override

    /**
     * Truncate = true will remove all records from report item table and skip the inividual deletion logic
     * @var bool
     */
    protected $truncateTable = false;

    protected $imageContentTypes = ['image'];

    public function __construct(
        EntityManagerInterface $em,
        Connection             $connection,
        ContentService         $contentService,
        UserService            $userService,
        PermissionResolver     $permissionResolver
    )
    {
        $this->em = $em;
        $this->connection = $connection;
        $this->contentService = $contentService;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
    }

    // query report data with search and filter pagination
    // download report data (all)

    public function setLogLevel(int $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    public function setSave($save)
    {
        if (is_bool($save)) {
            $this->save = $save;
        }
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setTruncateTable(bool $truncateTable): void
    {
        $this->truncateTable = $truncateTable;
    }

    public function setImageContentTypes(array $contentTypes): void
    {
        $this->imageContentTypes = $contentTypes;
    }

    public function loginUserOnProcess($username): void
    {
        // set current user
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin($username)
        );
    }

    public function generateReportItemFromOneContent($contentId): array
    {
        $this->log("Report items generating from 1 contentId ..");
        $results = [];
        if (!is_numeric($contentId)) {
            $this->log("contentID not number");
            return [];
        }
        $results[] = $this->parseContentToReportItems($contentId);
        $this->log("Report items [done]");
        return $results;
    }

    public function generateReportItems($limit, $offset): array
    {
        $countResult = $this->queryCountContent();
        $countResultTotal = $countResult->fetchOne();
        $this->log("Report items generating ..", 2);
        $rows = $this->queryContent($limit, $offset);
        $results = [];
        foreach ($rows as $row) {
            $contentId = $row['contentobject_id'];
            $results[] = $this->parseContentToReportItems($contentId);
        }
        $this->log("Total content available " . $countResultTotal, 2);
        $this->log("Report items [done]", 2);
        return $results;
    }

    /**
     * TO OVERRIDE
     */
    protected function queryCountContent()
    {
        // EXAMPLE ONLY: will not run
        $sql = "select 0";
        return $this->connection->executeQuery($sql);
    }

    /**
     * TO OVERRIDE
     * @param $limit
     * @param $offset
     * @throws Exception
     */
    protected function queryContent($limit = 1, $offset = 0)
    {
        // EXAMPLE ONLY: will not run
        $sql = "select distinct eco.id as contentobject_id
            from ezcontentobject eco, ezcontentobject_attribute ecoa
            where eco.id = ecoa.contentobject_id and eco.current_version = ecoa.version
            and 1 = 0
            order by eco.id limit :limit offset :offset";
        $sql = str_replace(":limit", $limit, $sql);
        $sql = str_replace(":offset", $offset, $sql);
        return $this->connection->executeQuery($sql);
    }

    /**
     * TO OVERRIDE
     * @param $contentId
     * @return array
     */
    protected function parseContentToReportItems($contentId): array
    {
        $parseContentResults = [];
        // Logic in child class ...
        return $parseContentResults;
    }

    /**
     * TO OVERRIDE
     * @param array $reportResults
     * @return void
     */
    public function storeReportResults(array $reportResults): void
    {
        // Logic in child class ...
    }

    public function purgeAllReportItems(): bool
    {
        if (!empty($this->tableName)) {
            $this->log(sprintf(" Missing tablename to truncate"), 1);
            return false;
        }
        $sql = sprintf("truncate %s", $this->tableName);
        $this->log(sprintf(" Truncate table %s", $this->tableName), 3, false);
        if ($this->save === true) {
            $this->connection->executeQuery($sql);
            $this->log(sprintf("[truncate]"));
        } else {
            $this->log(sprintf("[dry-run]"));
        }
        // also mark truncation happened for later operations
        $this->truncateTable = true;
        return true;
    }

    /**
     * @param string $message
     * @param bool $newLine
     */
    protected function log($message = '', $logLevel = 3, $newLine = true): void
    {
        if ($this->logLevel >= $logLevel) {
            if (is_object($this->output)) {
                if ($newLine) {
                    $this->output->writeln($message);
                } else {
                    $this->output->write($message);
                }
            } else {
                echo $message;
                if ($newLine) {
                    echo "\n";
                }
            }
        }
    }

}


?>