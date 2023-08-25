<?php


namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use Ibexa\Core\FieldType\ImageAsset\Value as ImageAssetValue;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateContentRelationsCommand extends Command {

    const STATUS_VALID = "valid";
    const STATUS_INVALID = "invalid";
    const STATUS_UNKNOWN = "unknown";
    const STATUS_ERROR = "error";

    protected Connection $connection;
    protected $contentService;
    protected $repository;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;

    protected $reportItems = array();

    public const COMMAND_NAME = 'app:validate-content:relations';

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection $connection,
        Repository $repository,
        ContentService $contentService,
        LoggerInterface $validateContentLogger
    )
    {
        parent::__construct(self::COMMAND_NAME);
        $this->connection = $connection;
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->logger = $validateContentLogger;
    }

    protected function configure(): void
    {
        $this
            
            ->setDescription('Validate Richtext')
            ->addOption(
                'content-id',
                null,
                InputOption::VALUE_REQUIRED,
                'specific Content ID; else query content by limit'
            )
            ->addOption(
                'offset',
                null,
                InputOption::VALUE_REQUIRED,
                'Query offset',
                0
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Query limit',
                10
            )
            ->addOption(
                'contentclass-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Query limit'
            )

        ;
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
        $contentClassId = $input->getOption('contentclass-id');
        if (!empty($contentClassId) && !is_numeric($contentClassId)) {
            throw new InvalidArgumentException('contentClass_id optional value has to be an integer.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $contentId = $input->getOption('content-id');
        $contentClassId = $input->getOption('contentclass-id');
        $offset = intval($input->getOption('offset'));
        $limit = intval($input->getOption('limit'));

        $output->writeln('Running ..');
        $output->writeln('');

        if(!empty($contentId) && is_numeric($contentId)) {
            $contentIdRows = array(array('id'=>$contentId));
        } else {
            $contentIdRows = $this->queryContentListByContentType($offset, $limit, $contentClassId);
        }

        $count = 0 + $offset;
        foreach($contentIdRows as $row) {
            $count++;
            /** @var Content $content */
            $content = $this->getContentById($row['id']);
            $logPrefix = "ContentId [".$content->id."] ".$content->getName();
            $output->writeln($logPrefix);
            // HOLD $this->logger->info($logPrefix);
            $this->checkContentFieldData($content);
        }

        // check report
        $this->displayReport();

        // Summary:
        echo "Query: count: ".$count." offset: ".$offset. " limit: ".$limit."\n";

        return Command::SUCCESS;

    }

    protected function checkContentFieldData(Content $content) {
        $cid = (int) $content->id;
        $fields = $content->getFields();
        foreach ($fields as $field) {
            $fieldReferenceContentIds = array();
            $fieldIdentifier = $field->getFieldDefinitionIdentifier();
            $fieldType = $field->getFieldTypeIdentifier();
            switch ($fieldType) {
                case 'ezrichtext': {
                    try{
                        // try the richtext
                        /** @var RichTextValue $value */
                        $value = $field->getValue();
                        // echo "length: ".strlen($value->xml->saveXML()). " ";
                        // TODO: extract fieldReferenceContentIds
                        // EMBED:
                        // TODO: extract locationIDs
                        // URL
                    } catch(\Exception $e) {
                        // echo "error..";
                        $this->reportItems[] = array(
                            'content_id'=>$cid,
                            'field'=>$fieldIdentifier,
                            'type'=>$fieldType,
                            'status'=>self::STATUS_ERROR
                        );
                    }
                    break;
                }
                case 'ezimageasset': {
                    /** @var ImageAssetValue $value */
                    $value = $field->getValue();
                    if(!empty($value->destinationContentId)) {
                        $fieldReferenceContentIds[] = $value->destinationContentId;
                    }
                    break;
                }
                /** TODO other extractors
                 * ezbinaryfile
                ezboolean
                ezdatetime
                ezemail
                ezform
                ezgmaplocation
                ezidentifier
                ezimage
                ezimageasset
                ezinisetting
                ezinteger
                ezkeyword
                ezlandingpage
                ezobjectrelation
                ezobjectrelationlist
                ezoption
                ezrichtext
                ezselection
                ezstring
                eztags
                eztext
                ezurl
                 */

            }

            // lookup contentIDs for this field
            foreach($fieldReferenceContentIds as $refContentId) {
                $status = $this->queryContentExists($refContentId);
                $this->reportItems[] = array(
                    'content_id'=>$cid,
                    'field'=>$fieldIdentifier,
                    'type'=>$fieldType,
                    'ref_content_id'=>$refContentId,
                    'status'=> $status);
            }

        }

        // lookup if contentId's found in fields and if they are valid / published
        // If invalid, then log error
    }

    protected function getContentById($contentId) {
        return $this->repository->sudo(
            function () use ($contentId) {
                return $this->contentService->loadContent($contentId);
            }
        );
    }


    protected function queryContentListByContentType($offset, $limit, $contentClassId){
        // get set of contentIDs
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id');
        $qb->from('ezcontentobject');
        if(!empty($contentClassId)){
            $qb->where('contentclass_id = :contentclass_id');
            $qb->setParameter('contentclass_id',$contentClassId);
        }
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->execute()->fetchAllAssociative();
    }

    protected function queryContentExists($contentId) {
        if(empty($contentId)) {
            return self::STATUS_UNKNOWN; // no check
        }
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id')
            ->from('ezcontentobject')
            ->where('id = :contentId')
            ->setParameter('contentId',$contentId);
        $result = $qb->execute()->fetchOne();
        if(!empty($result)){
            return self::STATUS_VALID;
        }
        return self::STATUS_INVALID;
    }

    protected function displayReport() {
        echo "--- Checks ---\n";
        foreach ($this->reportItems as $error) {
            // show NOT valid entries
            if($error['status'] !== self::STATUS_VALID) {
                $message = "";
                $message .= "content_id: ".$error['content_id']." ";
                $message .= "[".$error['field']."] ";
                $message .= "(".$error['type'].") ";
                if(array_key_exists('ref_content_id',$error)){
                    $message .= "ref_content_id: ".$error['ref_content_id']." ";
                }
                if(array_key_exists('status',$error)){
                    $message .= $error['status']." ";
                }
                $message .= "\n";
                // log errors to file
                $this->logger->error($message);
                $this->output->writeLn($message);
            }
        }
        echo "--- ------ ---\n";
    }
}

?>