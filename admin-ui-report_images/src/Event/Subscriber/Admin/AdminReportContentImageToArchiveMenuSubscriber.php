<?php declare(strict_types=1);

namespace App\Event\Subscriber\Admin;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MainMenuBuilder;
use Knp\Menu\MenuItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminReportContentImageToArchiveMenuSubscriber implements EventSubscriberInterface
{

    const MENU_KEY_REPORTS = 'Reports';
    const MENU_KEY_CONTENT_ACTIVITY = 'mail__reports__image_to_archive__menu';

    public static function getSubscribedEvents()
    {
        return [
            ConfigureMenuEvent::MAIN_MENU => ['onMainMenuConfigure', 0],
        ];
    }

    public function onMainMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        // TODO: use keys for translations instead of English

        /** @var MenuItem $contentMenu */
        $contentMenu = $menu[MainMenuBuilder::ITEM_CONTENT];
        $customMenuItem = $contentMenu->getChild(self::MENU_KEY_REPORTS);
        if (!is_object($customMenuItem)) {
            $customMenuItem = $contentMenu->addChild(
                self::MENU_KEY_REPORTS,
                [
                    self::MENU_KEY_REPORTS => [
                        'orderNumber' => 100,
                        'extras' => [
                            'translation_domain' => 'messages',
                        ],
                    ],
                ],
            );
        }


        $customMenuItem->addChild(
            self::MENU_KEY_CONTENT_ACTIVITY,
            [
                'label' => 'Images to Archive',
                'route' => 'admin-ui.reports.image_to_archive',
                'attributes' => [
                    'class' => 'custom-menu-item',
                ],
                'linkAttributes' => [
                    'class' => 'custom-menu-item-link',
                ],
            ]
        );

    }
}

?>