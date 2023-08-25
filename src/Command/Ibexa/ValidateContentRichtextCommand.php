<?php

/**
 * Author: David Sayre / Allegiance Group
 */

declare(strict_types=1);

namespace App\Command\Ibexa;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Repository;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateContentRichtextCommand extends Command
{

    protected Connection $connection;
    protected $contentService;
    protected $repository;
    protected $logger;

    protected InputInterface $input;
    protected OutputInterface $output;

    public const COMMAND_NAME = 'app:validate-content:richtext';


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
            
            ->setDescription('Validate Content Richtext')
            ->addOption(
                'content_id',
                'i',
                InputOption::VALUE_REQUIRED,
                'Content ID'
            )
            ->addOption(
                'offset',
                'o',
                InputOption::VALUE_REQUIRED,
                'Query offset',
                0
            )
            ->addOption(
                'limit',
                'm',
                InputOption::VALUE_REQUIRED,
                'Query limit',
                10
            )
            ->addOption(
                'contentclass_id',
                'l',
                InputOption::VALUE_REQUIRED,
                'Query limit'
            )

        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $contentId = $input->getOption('content_id');
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
        $contentClassId = $input->getOption('contentclass_id');
        if (!empty($contentClassId) && !is_numeric($contentClassId)) {
            throw new InvalidArgumentException('contentClass_id optional value has to be an integer.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $contentId = $input->getOption('content_id');
        $contentClassId = $input->getOption('contentclass_id');
        $offset = $input->getOption('offset');
        $limit = $input->getOption('limit');

        if(empty($offset) || !is_numeric($offset)) {
            $offset = 0;
        }
        if(empty($limit) || !is_numeric($limit)) {
            $limit = 100;
        }

        $output->writeln('Running ..');
        $output->writeln('');

        $errors = array();

        if(!empty($contentId) && is_numeric($contentId)) {
            $contentList = array(array('id'=>$contentId));
        } else {

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

            $contentList = $qb->execute()->fetchAllAssociative();
        }

        $count = 0 + $offset;
        foreach($contentList as $content) {
            $count++;
            $cid = (int) $content['id'];
            echo "Row #".$count." Content [".$cid. "] ";
            /** @var Content $content */
            $content = $this->repository->sudo(
                function () use ($cid) {
                    return $this->contentService->loadContent($cid);
                }
            );
            echo $content->getName()."\n";
            $fields = $content->getFields();
            foreach ($fields as $field) {
                $fieldIdentifier = $field->getFieldDefinitionIdentifier();
                if($field->fieldTypeIdentifier === 'ezrichtext') {
                    echo "  Field: ".$fieldIdentifier." ";
                    try{
                        // try the richtext
                        /** @var \Ibexa\FieldTypeRichText\FieldType\RichText\Value $value */
                        $value = $field->getValue();
                        echo "length: ".strlen($value->xml->saveXML()). " ";
                        $test = new RichTextValue($value->xml);
                        echo "[ok]";
                    } catch(\Exception $e) {
                        echo "error..";
                        $this->logger->error("content_id: ".$cid." | field: ".$fieldIdentifier."");
                        $errors[] = array('content_id'=>$cid,'field'=>$fieldIdentifier);
                    }
                    echo "\n";
                }
            }
        }

        echo "Query: offset ".$offset. " / limit ".$limit."\n";

        // get content object
        // loop over field

        foreach ($errors as $error) {
            echo "Content_id : ".$error['content_id']." field: ".$error['field']."\n";
        }

        return Command::SUCCESS;

    }

}

