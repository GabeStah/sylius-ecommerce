<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
  public function addAdminMenuItems(MenuBuilderEvent $event): void
  {
    $menu = $event->getMenu();

    $newSubmenu = $menu->addChild('custom')->setLabel('Miscellaneous');

    $newSubmenu
      ->addChild('dealers', ['route' => 'app_admin_dealer_index'])
      ->setLabel('Dealers')
      ->setLabelAttribute('icon', 'warehouse');
  }
}
