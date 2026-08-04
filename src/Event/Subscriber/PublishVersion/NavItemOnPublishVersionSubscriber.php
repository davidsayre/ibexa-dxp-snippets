<?php
declare(strict_types=1);

namespace App\Event\Subscriber\PublishVersion;

use App\Services\PurgeClientService;
use Ibexa\Contracts\Core\Repository\Events\Content\PublishVersionEvent;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NavItemOnPublishVersionSubscriber implements EventSubscriberInterface
{
    const CONTENT_TYPE_IDENTIFIER = 'nav_item';

    // App service injected from services.yaml
    protected PurgeClientInterface $purgeClient;

    public function __construct(
        PurgeClientService $purgeClientService
    ) {
        $this->purgeClient = $purgeClientService->getClient();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PublishVersionEvent::class => 'onPublishVersionEvent',
        ];
    }

    public function onPublishVersionEvent(PublishVersionEvent $event): void
    {
        $content = $event->getContent();
        // purge all pages tagged with content type matching 'nav_item'
        $contentType = $content->getContentType();
        if ($contentType->identifier === self::CONTENT_TYPE_IDENTIFIER) {
            try {
                $tags = [ContentTagInterface::CONTENT_TYPE_PREFIX . $contentType->id];
                $this->purgeClient->purge($tags);
            } catch(\Exception $e) {
                 // do nothing
            }
        }
    }

}
