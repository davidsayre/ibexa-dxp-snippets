<?php

namespace App\Command\Ibexa;

use App\Services\Email\FormBuilderSendEmailService;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\FormBuilder\FieldType\Model\FormSubmissionList;
use Ibexa\Contracts\FormBuilder\FormSubmission\FormSubmissionServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ibexa\Contracts\FormBuilder\FieldType\Model\Field;

class FormBuilderExportSubmissionsCommand extends Command {

    protected static $defaultName = 'app:form-builder:export-submissions';

    public function __construct(
        private ContentService $contentService,
        private FormSubmissionServiceInterface $formSubmissionService,
    )
    {


        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription("Form Builder export submissions")
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('content-id', null, InputOption::VALUE_REQUIRED, "Content ID of Form Builder object"),
                ))
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int {

        $output->writeln("[Start]");

        $formContentId = $input->getOption('content-id');

        if(empty($formContentId)) {
            $output->writeln("Form Content ID is required");
            return Command::FAILURE;
        }

        try {
            $formContent = $this->contentService->loadContent($formContentId);
            /** @var FormSubmissionList $submissions */
            $submissions = $this->formSubmissionService->loadByContent($formContent->getContentInfo());
            $output->writeln('Total number of submissions: ' . $submissions->getTotalCount());
            foreach ($submissions as $sub) {
                $output->writeln(sprintf("-- %s submitted on %s", $sub->getId(),$sub->getCreated()->format('Y-m-d H:i:s')));
                /** @var \Ibexa\Contracts\FormBuilder\FieldType\Model\FieldValue $value */
                foreach ($sub->getValues() as $value) {
                    if(is_object($value)) {
                        $output->writeln(sprintf("%d,%s,%s,%s", $sub->getId(), $value->getName(), $value->getIdentifier(), json_encode($value->getValue())));
                    }
                }
                $output->writeln("--");
            }
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }
        $output->writeln("[Complete]");

        return Command::SUCCESS;
    }
}

?>