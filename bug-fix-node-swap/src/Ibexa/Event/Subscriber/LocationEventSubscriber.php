<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);


use Ibexa\Contracts\Core\Repository\Events\Location\BeforeSwapLocationEvent;
use Ibexa\Core\Search\Common\EventSubscriber\AbstractSearchEventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LocationEventSubscriber extends AbstractSearchEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeSwapLocationEvent::class => 'onBeforeSwapLocation',
        ];
    }

    public function onBeforeSwapLocation(BeforeSwapLocationEvent $event): void
    {
        $location1 = $event->getLocation1();
        $location2 = $event->getLocation2();

        $this->searchHandler->deleteLocation(
            $location1->id,
            $location1->contentId
        );

        $this->searchHandler->deleteLocation(
            $location2->id,
            $location2->contentId
        );
    }
}
