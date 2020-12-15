<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class AboutController extends AbstractController
{
  public function indexAction(): Response
  {
    return $this->render('bundles/SyliusShopBundle/About/index.html.twig');
  }
}
