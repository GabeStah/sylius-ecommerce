<?php

namespace App\Menu;

use Sylius\Bundle\AdminBundle\Event\ProductMenuBuilderEvent;

final class AdminProductFormMenuListener
{
  public function addItems(ProductMenuBuilderEvent $event): void {
    $menu = $event->getMenu();

    $menu
      ->addChild('files', ['route' => 'app_admin_files_index'])
      ->setAttribute('template', 'Admin/Product/Tab/_files.html.twig')
      ->setLabel('Files');
  }
}
