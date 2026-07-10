<?php
declare(strict_types=1);

namespace App\Event\Subscriber\PublishVersion;

use Ibexa\Contracts\Core\Repository\Events\Content\PublishVersionEvent;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/* NOTE: must add src/config/services.yaml entry to inject $purgeClient */

class NavItemOnPublishVersionSubscriber implements EventSubscriberInterface
{
    const CONTENT_TYPE_IDENTIFIER = 'nav_item';

    protected $purgeClient;

    public function __construct(
        // PurgeClientInterface $purgeClient
    ) {
        // $this->purgeClient = $purgeClient;
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
                // $this->purgeClient->purge($tags);
            } catch(\Exception $e) {
                // do nothing
            }
        }
    }

}
