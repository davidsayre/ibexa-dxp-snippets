<?php
declare(strict_types=1);

namespace App\Event\Subscriber\PublishVersion;

use Ibexa\Contracts\Core\Repository\Events\Content\PublishVersionEvent;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;

class NavItemOnPublishVersionSubscriber implements EventSubscriberInterface
{
    const CONTENT_TYPE_IDENTIFIER = 'nav_item';

    public static function getSubscribedEvents(): array
    {
        return [
            PublishVersionEvent::class => 'onPublishVersionEvent',
        ];
    }

    public function onPublishVersionEvent(PublishVersionEvent $event, PurgeClientInterface $purgeClient): void
    {
        $content = $event->getContent();
        // purge all pages tagged with content type matching 'nav_item'
        $contentType = $content->getContentType();
        if ($contentType->identifier === self::CONTENT_TYPE_IDENTIFIER) {
            $tags = [ContentTagInterface::CONTENT_TYPE_PREFIX . $contentType->id];
            $purgeClient->purge($tags);
        }
    }

}
