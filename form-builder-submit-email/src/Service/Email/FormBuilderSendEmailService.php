<?php

namespace App\Services\Email;

use App\Message\FormBuilderSendEmailMessage;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\FormBuilder\Event\FormSubmitEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Helper\Slugify;
use Ibexa\Core\FieldType\Url\Value as UrlValue;
use Ibexa\Core\FieldType\TextLine\Value as TextLineValue;

class FormBuilderSendEmailService
{

    private MessageBusInterface $bus;
    private BodyRenderer $bodyRenderer;
    private ParameterBagInterface $parameterBag;
    private LoggerInterface $logger; // magic name map
    private ContentService $contentService;

    public function __construct(MessageBusInterface $bus, BodyRenderer $bodyRenderer, ParameterBagInterface $parameterBag, LoggerInterface $formbuilderuseremailLogger, ContentService $contentService)
    {
        $this->bus = $bus;
        $this->bodyRenderer = $bodyRenderer;
        $this->parameterBag = $parameterBag;
        $this->logger = $formbuilderuseremailLogger;
        $this->contentService = $contentService;
    }

    public function onSubmit(FormSubmitEvent $event): void
    {

        $formFields = $event->getForm()->getFields();
        $eventData = $event->getData();

        $languageCode = "eng-US";
        if (array_key_exists('languageCode', $eventData)) {
            $languageCode = $eventData['languageCode'];
        }
        $formData = $eventData['fields'];
        $formContentId = $eventData['contentId']; // form being submitted's content ID, used for lookup in config

        $templateFile = $this->extractConfigParamByContentKey($formContentId, 'template');
        $emailFieldName = $this->extractConfigParamByContentKey($formContentId, 'field');

        // (Optional) add content fields to workflow
        $extraContentParams = $this->extractExtraTemplateParams($formContentId);

        // Use default or override from optional content
        $emailSubject = $this->buildEmailSubject($formContentId, $extraContentParams);

        // merge field data with form fields into simplified holder
        $fieldData = $this->extractFormFieldData($formFields, $formData);

        // extract 'email' field value for toEmail
        $toEmail = $this->extractEmailFieldValue($fieldData, $emailFieldName);

        // get/check required params before sending
        if (empty($templateFile)) {
            $this->logger->error(sprintf("form %s empty template param", $formContentId));
            return;
        }
        if (empty($emailFieldName)) {
            $this->logger->error(sprintf("form %s empty field param", $formContentId));
            return;
        }
        if (empty($emailSubject)) {
            $this->logger->error(sprintf("form %s empty subject param", $formContentId));
            return;
        }
        if (empty($toEmail)) {
            $this->logger->error(sprintf("form %s empty email param", $formContentId));
            return;
        }

        // holder for template params (data.X)
        $templateData = [
            'languageCode' => $languageCode,
            // 'formContentId' => $formContentId,
            'fields' => $fieldData,
            'config' => $extraContentParams,
        ];

        // initiate email send (async)
        $this->sendTemplatedEmail($templateFile, null, $toEmail, $emailSubject, $templateData);
    }

    public function sendTemplatedEmail(string $templateFile, ?string $fromEmail, string $toEmail, string $subject, array $templateData)
    {
        $this->logger->info(sprintf("Queue email to %s : %s", $toEmail, $subject));
        try {
            $templateParams = [
                'toEmail' => $toEmail,
                'fromEmail' => $fromEmail,
                'subject' => $subject,
                'data' => $templateData,
            ];
            $email = (new TemplatedEmail())
                // ->from() is pulled from mailer.yaml
                ->to($toEmail)
                ->subject($subject)
                //->text('Text template...')
                // pass simple array variables (name => value) to the template (Never doctrine objects)
                ->context($templateParams)
                ->htmlTemplate($templateFile);
            $this->bodyRenderer->render($email); // convert into flat email rendered for async
            $this->bus->dispatch(new FormBuilderSendEmailMessage($email));
            $this->logger->info(sprintf("Queue email to %s : %s success", $toEmail, $subject));
        } catch (\Exception $e) {
            $this->logger->error(sprintf("Queue email to %s : %s failed", $toEmail, $subject));
            $this->logger->error($e->getMessage());
            return $e;
        }
        return true;
    }

    public function buildEmailSubject($formContentId, array $extraTemplateParams = []) : string
    {
        $emailSubject = $this->extractConfigParamByContentKey($formContentId, 'subject'); // default email subject
        if (is_array($extraTemplateParams) && array_key_exists('email_subject', $extraTemplateParams) && !empty($extraTemplateParams['email_subject'])) {
            $emailSubject = $extraTemplateParams['email_subject'];
        }
        return $emailSubject;
    }

    public function extractConfigParamByContentKey($formContentId, $key): string|array
    {
        $paramString = sprintf('app.form_builder.email_user.%d.%s', $formContentId, $key);
        if (!$this->parameterBag->has($paramString)) {
            $this->logger->error(sprintf("form %s missing %s param", $formContentId, $paramString));
            return "";
        }
        $value = $this->parameterBag->get($paramString);
        if (empty($value)) {
            $this->logger->error(sprintf("form %s empty %s param", $formContentId, $paramString));
            return "";
        }
        return $value;
    }

    public function extractFormFieldData(array $fields, array $formData): array
    {
        $slugify = new Slugify();
        $fieldData = [];
        /** @var \Ibexa\Contracts\FormBuilder\FieldType\Model\Field $field */
        foreach ($fields as $field) {
            $key = $slugify->slugify($field->getName(), "_"); // only name is available for a key so slugify with _
            $fieldData[$key] = [
                'type_identifier' => $field->getIdentifier(),
                'key' => $key,
                'id' => $field->getId(),
                'name' => $field->getName(),
                // Can be array,string,null
                'value' => $formData[$field->getId()] ?? null,
            ];
        }
        return $fieldData;
    }

    public function extractEmailFieldValue(array $fieldData, string $fieldName): string
    {
        if (!array_key_exists($fieldName, $fieldData)) {
            return "";
        }
        if (!array_key_exists('value', $fieldData[$fieldName])) {
            return "";
        }
        if (empty($fieldData[$fieldName]['value'])) {
            return "";
        }
        return $fieldData[$fieldName]['value'];
    }

    /**
     * @param int $contentId - form content ID to lookup config params
     * @return array
     */
    public function extractExtraTemplateParams(int $formContentId)
    {

        $addContentId = $this->extractConfigParamByContentKey($formContentId, 'add_content.id');
        $addContentFieldIdentifiers = $this->extractConfigParamByContentKey($formContentId, 'add_content.fields');

        $extraTemplateParams = [];

        try {
            if (!empty($addContentId) && !empty($addContentFieldIdentifiers)) {
                $addContent = $this->contentService->loadContent($addContentId);
                if (is_object($addContent)) {
                    foreach ($addContentFieldIdentifiers as $fieldIdentifier) {
                        $field = $addContent->getField($fieldIdentifier);
                        if (!is_object($field)) {
                            continue;
                        }
                        $fieldValue = $field->getValue();
                        // Extract based on the Field Value Object type
                        // Url
                        if (is_a($fieldValue, UrlValue::class)) {
                            $extraTemplateParams[$fieldIdentifier] = $fieldValue->link;
                            continue;
                        }
                        // TextLine
                        if (is_a($fieldValue, TextLineValue::class)) {
                            $extraTemplateParams[$fieldIdentifier] = $fieldValue->text;
                            continue;
                        }
                        // TBD more types if needed but be careful of complexity
                    }
                } else {
                    $this->logger->error(sprintf("add content %s not found", $addContentId));
                }
            }

        } catch (\Exception $e) {

        }

        return $extraTemplateParams;
    }
}

?>