## sending form builder messages using symfony messenger

1. create src/MessageHandler/FormBuilderSendEmailMessageHandler.php

``` 
<?php

namespace App\MessageHandler;

use App\Message\FormBuilderSendEmailMessage;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;



/**
 * messenger.yaml "App\Message\FormBuilderSendEmailMessage: async" will queue emails
 * Therefore this is not wired up / used right now but is functional if needed
 */
#[AsMessageHandler]
final class FormBuilderSendEmailMessageHandler implements MessageHandlerInterface
{
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function __invoke(FormBuilderSendEmailMessage $message): ?SentMessage
    {
        return $this->transport->send($message->getMessage(), $message->getEnvelope());
    }
}
```

2. create message src/Message/FormBuilderSendEmailMessage.php
``` 
 <?php

namespace App\Message;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;

final class FormBuilderSendEmailMessage
{
    private $message;
    private $envelope;

    public function __construct(RawMessage $message, ?Envelope $envelope = null)
    {
        $this->message = $message;
        $this->envelope = $envelope;
    }

    public function getMessage(): RawMessage
    {
        return $this->message;
    }

    public function getEnvelope(): ?Envelope
    {
        return $this->envelope;
    }
}
```

3. setup .yaml files
config/mailer.yaml
``` 
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
        envelope:
            sender: '%env(SENDER_ADDRESS)%'
            #recipients: ['foo@example.com', 'bar@example.com']
        headers:
            From: '%env(SENDER_ADDRESS)%'
#            Bcc: 'user@company.com'
#            X-Custom-Header: 'foobar' 
```

config/packages/messenger.yaml
``` 
framework:
  messenger:
    # reset services after consuming messages
    reset_on_message: true

    # Uncomment this (and the failed transport below) to send failed messages to this transport for later handling.
    failure_transport: failed

    transports:
      # https://symfony.com/doc/current/messenger.html#transport-configuration
      async: '%env(MESSENGER_TRANSPORT_DSN)%'
      failed: 'doctrine://default?queue_name=failed'
      # sync: 'sync://'

    routing:
      Symfony\Component\Mailer\Messenger\SendEmailMessage: async
      # Queue async message (class)
      App\Message\FormBuilderSendEmailMessage: async
```

4. create src/Services/FormBuilderSendEmailSerivice.php
``` 
<?php

namespace App\Services\Email;

use App\Message\FormBuilderSendEmailMessage;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;

class FormBuilderSendEmailService
{

    private MessageBusInterface $bus;
    private BodyRenderer $bodyRenderer;

    public function __construct(MessageBusInterface $bus, BodyRenderer $bodyRenderer)
    {
        $this->bus = $bus;
        $this->bodyRenderer = $bodyRenderer;
    }

    public function sendTemplatedEmail(string $templateFile, string $fromEmail, string $toEmail, string $subject, array $data)
    {
        try {
            $templateParams = [
                'toEmail' => $toEmail,
                'fromEmail' => $fromEmail,
                'subject' => $subject,
                'data' => $data
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
        } catch (\Exception $e) {
            return $e;
        }
        return true;
    }
}

?> 
```


5. create src/Event/Subscriber/FormBuilderSubmitSubscriber.php
``` 
<?php declare(strict_types=1);

namespace App\Event\Subscriber;

use App\Services\Email\FormBuilderSendEmailService;
use Ibexa\FormBuilder\Event\FormSubmitEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FormBuilderSubmitSubscriber implements EventSubscriberInterface
{
    private FormBuilderSendEmailService $formBuilderSendEmailService;
    public function __construct(FormBuilderSendEmailService $formBuilderSendEmailService)
    {
        $this->formBuilderSendEmailService = $formBuilderSendEmailService;
    }

    /**
     * @return string[]|null
     */
    public static function getSubscribedEvents(): ?array
    {
        return [FormSubmitEvent::class => 'onSubmit'];
    }

    public function onSubmit(FormSubmitEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();
        $view = $event->getContentView();
        
        /*
            // TODO: extract email field
            // TODO: some logic for knowing which forms to send and which not to...
            
            // EXAMPLE:  
            $templateFile = 'themes/standard/test/email.html.twig';
            $fromEmail = 'someuser@localhost';
            $toEmail = 'someuser@localhost';
            $subject = "test email";
            $data = ['var'=>'something'];
            $success = $this->formBuilderSendEmailService->sendTemplatedEmail($templateFile, $fromEmail, $toEmail, $subject, $data);
        */
        
       
    }
}
?> 
```