<?php

/**
 * Author: David Sayre / Allegiance Group
 */

namespace App\Command\Ibexa;

use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check each content object's RichText and ImageAsset Fields for invalid relations
 *
 * example: bin/console app:check-richtext --file=mydata.xml
 */
class CheckRichtextXMLCommand extends Command
{

    protected InputInterface $input;
    protected OutputInterface $output;


    public const COMMAND_NAME = 'app:check-richtext';

    protected function configure(): void
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Check Richtext parsing XML file')
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'XML filename to read and check'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $fileName = $input->getOption('file');

        $xmlData = $this->readXmlFile($fileName);
        if(!empty($xmlData)) {
            $this->validateRichTextField($xmlData);
        } else {
            $output->writeln("No XML file data");
        }

        return Command::SUCCESS;

    }

    protected function readXMLFile($fileName) {
        $xmlData = "";
        if(file_exists($fileName)) {
            $this->output->writeln("Reading ".$fileName);
            $xmlData = file_get_contents($fileName);
        } else {
            $this->output->writeln("File not found ".$fileName);
        }
        return $xmlData;
    }

    protected function validateRichTextField($xml)
    {
        try {
            print_r($xml);
            $this->output->writeln("Testing XML with length ".strlen($xml)." characters");
            $test = new RichTextValue($xml);
            print_r($test->xml->saveXML());
            //print_r($test);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->output->writeln("ERROR " . $errorMessage);
        }
    }


}


?>
