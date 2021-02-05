<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use FOS\RestBundle\View\View;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends ResourceController
{
  public function indexAction(Request $request): Response
  {
    $configuration = $this->requestConfigurationFactory->create(
      $this->metadata,
      $request
    );

    $this->isGrantedOr403($configuration, ResourceActions::INDEX);
    $resources = $this->resourcesCollectionProvider->get(
      $configuration,
      $this->repository
    );

    $this->eventDispatcher->dispatchMultiple(
      ResourceActions::INDEX,
      $configuration,
      $resources
    );

    $taxonRepository = $this->container->get('sylius.repository.taxon');

    $isProductVisible = true;

    if (
      $request->get('_route') !== 'sylius_shop_partial_product_index_latest'
    ) {
      $foundTaxon = $taxonRepository->findOneBySlug(
        $request->get('slug'),
        $this->container->get('sylius.context.locale')->getLocaleCode()
      );
      $isProductVisible = $foundTaxon->isProductVisible();
    }

    $view = View::create($resources);

    if ($configuration->isHtmlRequest()) {
      $view
        ->setTemplate(
          $configuration->getTemplate(ResourceActions::INDEX . '.html')
        )
        ->setTemplateVar($this->metadata->getPluralName())
        ->setData([
          'configuration' => $configuration,
          'metadata' => $this->metadata,
          'resources' => $resources,
          'product_visible' => $isProductVisible,
          $this->metadata->getPluralName() => $resources,
        ]);
    }

    return $this->viewHandler->handle($configuration, $view);
  }
}
