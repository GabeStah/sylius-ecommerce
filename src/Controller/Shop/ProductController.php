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

    if ($request->get('_route') === 'sylius_shop_product_index') {
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

  public function showAction(Request $request): Response
  {
    $configuration = $this->requestConfigurationFactory->create(
      $this->metadata,
      $request
    );

    $this->isGrantedOr403($configuration, ResourceActions::SHOW);
    $product = $this->findOr404($configuration);

    $this->eventDispatcher->dispatch(
      ResourceActions::SHOW,
      $configuration,
      $product
    );

    $view = View::create($product);

    if ($configuration->isHtmlRequest()) {
      $view
        ->setTemplate(
          $configuration->getTemplate(ResourceActions::SHOW . '.html')
        )
        ->setTemplateVar($this->metadata->getName())
        ->setData([
          'configuration' => $configuration,
          'metadata' => $this->metadata,
          'resource' => $product,
          $this->metadata->getName() => $product,
        ]);
    }

    return $this->viewHandler->handle($configuration, $view);
  }
}
