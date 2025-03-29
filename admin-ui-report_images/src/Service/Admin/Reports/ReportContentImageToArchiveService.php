<?php

namespace App\Service\Admin\Reports;

use App\Entity\Admin\ReportItemImageArchive;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\UserService;

class ReportContentImageToArchiveService extends ReportContentService
{

    protected $tableName = "report_item_image_archive";
    protected SectionService $sectionService;
    protected $archiveSectionIds = [];

    public function __construct(
        EntityManagerInterface $em,
        Connection             $connection,
        ContentService         $contentService,
        UserService            $userService,
        PermissionResolver     $permissionResolver,
        SectionService         $sectionService
    )
    {
        $this->sectionService = $sectionService;
        parent::__construct($em, $connection, $contentService, $userService, $permissionResolver);
    }

    public function setArchiveSections(array $sectionNames): void
    {
        foreach ($sectionNames as $sectionName) {
            $section = $this->sectionService->loadSectionByIdentifier($sectionName);
            $this->archiveSectionIds[] = $section->id;
            $this->output->writeln("Set archive section: [$section->id] {$sectionName}");
        }
    }

    /**
     * Override parent()
     * @return \Doctrine\DBAL\ForwardCompatibility\DriverResultStatement|\Doctrine\DBAL\ForwardCompatibility\DriverStatement|\Doctrine\DBAL\ForwardCompatibility\Result
     * @throws \Doctrine\DBAL\Exception
     */
    protected function queryCountContent()
    {
        $sql = "select count(distinct eco.id) as total
            from ezcontentobject eco, ezcontentclass ecc
            where eco.contentclass_id = ecc.id and ecc.identifier in (:classes)";
        $classesList = sprintf("'%s'", implode("','", $this->imageContentTypes));
        $sql = str_replace(":classes", $classesList, $sql);

        return $this->connection->executeQuery($sql);
    }

    /**
     * Override parent()
     * @param $limit
     * @param $offset
     * @return \Doctrine\DBAL\ForwardCompatibility\DriverResultStatement|\Doctrine\DBAL\ForwardCompatibility\DriverStatement|\Doctrine\DBAL\ForwardCompatibility\Result
     * @throws \Doctrine\DBAL\Exception
     */
    protected function queryContent($limit = 1, $offset = 0)
    {
        $sql = "select distinct eco.id as contentobject_id
            from ezcontentobject eco, ezcontentclass ecc
            where eco.contentclass_id = ecc.id and ecc.identifier in (:classes)
            order by eco.id 
            limit :limit 
            offset :offset";
        $classesList = sprintf("'%s'", implode("','", $this->imageContentTypes));
        $sql = str_replace(":classes", $classesList, $sql);
        $sql = str_replace(":limit", $limit, $sql);
        $sql = str_replace(":offset", $offset, $sql);
        return $this->connection->executeQuery($sql);
    }

    /**
     * Override parent()
     * @param $contentId
     * @return array
     */
    protected function parseContentToReportItems($contentId): array
    {
        $parsedResult = [];
        $parsedResult['content_id'] = $contentId;
        // can add more details into parseResults if needed
        try {
            $this->log(sprintf("Parse image [%s] ", $contentId), 3, false);
            $content = $this->contentService->loadContent($contentId);
            $this->log(sprintf("'%s' ", $content->getName()), 3, false);

            $item = new ReportItemImageArchive();
            $item->setContentId($contentId);
            $item->setLanguage($content->versionInfo->initialLanguageCode);
            $item->setVersion($content->versionInfo->versionNo);

            $contentRelationsCount = $this->contentService->countRelations($content->versionInfo);
            $parsedResult['relations_count'] = $contentRelationsCount;
            $this->log(sprintf("%s relations ", $contentRelationsCount), 3, false);
            $contentReverseRelationsCount = $this->contentService->countReverseRelations($content->contentInfo);
            $parsedResult['reverse_relations_count'] = $contentRelationsCount;
            $this->log(sprintf("%s reverse relations ", $contentReverseRelationsCount), 3, false);

            // Default check any relations
            if ($contentReverseRelationsCount === 0 && $contentRelationsCount === 0) {
                $item->setStatus(ReportItemImageArchive::STATUS_NOT_IN_USE);
            }

            $parsedResult['reverse_relations'] = [];

            // Ignore relations (what this image uses)
            //if($contentRelationsCount > 0) {
            //$contentRelations = $this->contentService->loadRelations($content->versionInfo);
            //}

            // Validate reverse relations (what is using this image)
            if ($contentReverseRelationsCount > 0) {
                $item->setStatus(ReportItemImageArchive::STATUS_IN_USE);
                $contentReverseRelations = $this->contentService->loadReverseRelations($content->contentInfo);
                $countReverseRelationsInArchiveSections = 0; // count reverse relations within archived sections
                // HOLD debug $tmpReverseRelation = [];
                foreach ($contentReverseRelations as $contentReverseRelation) {
                    $sourceContentInfo = $contentReverseRelation->getSourceContentInfo();
                    if(
                        $sourceContentInfo->isTrashed() ||
                        array_search($sourceContentInfo->getSectionId(),$this->archiveSectionIds, true) !== false) {
                        $countReverseRelationsInArchiveSections++;
                    }
                    // HOLD debug
//                    $tmpReverseRelation['type'] = $contentReverseRelation->type;
//                    $tmpReverseRelation['field_name'] = $contentReverseRelation->sourceFieldDefinitionIdentifier;
//                    $tmpReverseRelation['name'] = $sourceContentInfo->getName();
//                    $tmpReverseRelation['section_id'] = $sourceContentInfo->getSectionId();
//                    $tmpReverseRelation['status'] = $sourceContentInfo->status;
//                    $tmpReverseRelation['is_trashed'] = $sourceContentInfo->isTrashed();
//                    $parseContentResults['reverse_relations'][] = $tmpReverseRelation;

                }
                if(count($contentReverseRelations) === $countReverseRelationsInArchiveSections) {
                    $item->setStatus(ReportItemImageArchive::STATUS_IN_USE_ARCHIVE_ONLY);
                }
            }

            $parsedResult['item'] = $item; // store
            $parsedResult['success'] = true;
            $this->log('', 3); // end line
        } catch (\Exception $e) {
            $this->log(sprintf("[error] %s", $e->getMessage()), 3); // end line
            $parsedResult['error'] = true;
        }

        return $parsedResult;
    }

    public function storeReportResults(array $reportResults): void
    {
        $this->log("Parsing report results > report items ",1);
        $saveCount = 0;
        foreach($reportResults as $result) {
            /** @var ReportItemImageArchive $reportItem */
            $reportItem = $result['item'];
            $saveCount++;
            if ($reportItem instanceof ReportItemImageArchive) {
                if($this->save === true) {
                    $this->em->persist($reportItem);
                    $this->log('.',3,false);
                    if($saveCount % 10 === 0) {
                        $this->em->flush();
                        $this->log("[save]",3,false);
                    }
                } else {
                    $this->log('d',3, false);
                }
            } else {
                $this->log("invalid item type",1); // end line
            }
        }

        if($this->save === true) {
            $this->em->flush();
        }
        if($this->save === true) {
            $this->log(sprintf("Storing [done]"),1);
        } else {
            $this->log(sprintf("Storing [dry-run]"),1);
        }
    }

    /**
     * Call from Admin UI Controller
     * @param $limit
     * @param $offset
     * @return array|\mixed[][]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function queryReportItemsWithContentFields($limit = 100, $offset = 0) {
        $sql = "select 
            ri.id as ri_id,
            ri.content_id,            
            ri.version,
            ri.language,
            ri.status,
            eco.name as content_name,            
            eco.published,
            eco.modified,
            eco.current_version,            
            eco.section_id
            from :tablename ri , ezcontentobject eco where ri.content_id = eco.id
            order by eco.id desc
            ";
        $sql = str_replace(":tablename", $this->tableName, $sql);
        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }

    public function queryReportItemsGroupByStatus() {
        $sql = "SELECT count(*) as total, status from :tablename group by status";
        $sql = str_replace(":tablename", $this->tableName, $sql);
        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }

}