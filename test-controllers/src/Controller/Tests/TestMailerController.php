<?php

namespace App\Controller\Tests;

use Ibexa\Bundle\Core\Controller;
use Swift_Mailer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * See routes.yaml for paths and config
 */

/*
    # config/routes.yaml
    TestEmailController:
        path:   /_test/email/send
        defaults:  {
            _controller: App\Controller\Tests\TestMailerController:testEmail,
            protectToken : '823451242345123',
            emailTo : 'myname@mycompany.com',
            emailFrom' : 'webmaster@mycompany.org',
            body : "test smtp"

*/

class TestMailerController extends Controller {

    private $defaultEmailTo = "no-reply@localhost"; // configure in route.yaml entry
    private $defaultEmailFrom = "no-reply@localhost"; // configure in route.yaml entry
    private $defaultBody = "TestMailerController() body";
    private $mailer;

    public function __construct(
        Swift_Mailer $mailer,
        ParameterBagInterface $parameterBag
    ) {
        $this->mailer = $mailer;
    }

    /**
     * @param Request $request
     * @return Response
    */
    public function testEmail(
        Request $request,
        string $protectToken  = "",
        string $emailTo = "",
        string $emailFrom = "",
        string $body = ""
    ) {

        if(empty($emailTo)) {
            $emailTo = $this->defaultEmailTo;
        }
        if(empty($emailFrom)) {
            $emailFrom = $this->defaultEmailFrom;
        }
        if(empty($body)) {
            $body = $this->defaultBody;
        }
        // no default protect token

        // Only send a test if the ?token is correctly provided to prevent abuse / bots
        if( empty($emailTo) || empty($emailFrom) || empty($protectToken) ) {
            return new Response("missing routes.yaml parameters please fix");
        }
        if( empty($protectToken) || $request->query->get('token') !== $protectToken) {
            return new Response("invalid token !".$protectToken." go away");
        }

        $message = (new \Swift_Message('Test Email'))
        ->setFrom($emailFrom)
        ->setReplyTo($emailFrom)
        ->setTo($emailTo)
        ->setBody(
            $body,
        "text/plain"
        );

        $this->mailer->send($message);

        $response = new Response();
        $response->setContent('Sending email to '.$emailTo);
        return $response;
    }

}
?>