<?php declare(strict_types=1);


use App\Services\Email\FormBuilderSendEmailService;
use Ibexa\FormBuilder\Event\FormEvents;
use Ibexa\FormBuilder\Event\FormSubmitEvent;
use Ibexa\FormBuilder\Exception\FormFieldNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FormBuilderSubmitSubscriber implements EventSubscriberInterface
{
    private FormBuilderSendEmailService $formBuilderSendEmailService;
    private ParameterBagInterface $parameterBag;
    public function __construct(FormBuilderSendEmailService $formBuilderSendEmailService, ParameterBagInterface $parameterBag)
    {
        $this->formBuilderSendEmailService = $formBuilderSendEmailService;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return string[]|null
     */
    public static function getSubscribedEvents(): ?array
    {
        return [
            FormEvents::FORM_SUBMIT => 'onSubmit'
        ];
    }

    public function onSubmit(FormSubmitEvent $event): void
    {
        $this->formBuilderSendEmailService->onSubmit($event);
    }
}
?>