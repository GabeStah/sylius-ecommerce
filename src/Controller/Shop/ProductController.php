<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use App\Entity\File\File;
use App\Entity\Product\Product;
use App\Entity\Product\ProductFile;
use App\Repository\FileRepository;
use App\Repository\ProductFileRepository;
use FOS\RestBundle\View\View;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductController extends ResourceController
{
  public function indexAction(Request $request): Response {
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
        $this->container->get('sylius.context.locale')
                        ->getLocaleCode()
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
          'configuration'                  => $configuration,
          'metadata'                       => $this->metadata,
          'resources'                      => $resources,
          'product_visible'                => $isProductVisible,
          $this->metadata->getPluralName() => $resources,
        ]);
    }

    return $this->viewHandler->handle($configuration, $view);
  }

  public function showAction(Request $request): Response {
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
          'configuration'            => $configuration,
          'metadata'                 => $this->metadata,
          'resource'                 => $product,
          $this->metadata->getName() => $product,
        ]);
    }

    return $this->viewHandler->handle($configuration, $view);
  }

  /**
   * Handles new file upload(s).
   *
   * @param Product              $resource
   * @param Request              $request
   * @param RequestConfiguration $configuration
   */
  private function handleFiles(
    Product $resource,
    Request $request,
    RequestConfiguration $configuration
  ) {
    if ($request->files->count() >= 1) {
      $uploader = $this->container->get('app.listener.admin.file_upload');

      foreach (
        $request->files->get('sylius_product')['files']
        as $uploadedFile
      ) {
        // Upload file to filesystem
        $localPath = $uploader->upload($uploadedFile);

        $file = new File();
        $file->setFile(
          new \Symfony\Component\HttpFoundation\File\File($localPath)
        );
        $file->hydrate();

        /** @var FileRepository $fileRepository */
        $fileRepository = $this->container->get('app.repository.files');
        // Find existing file by matching checksum
        $existingFile = $fileRepository->findOneBy([
          'checksum' => $file->getChecksum(),
        ]);
        if ($existingFile) {
          $this->flashHelper->addErrorFlash(
            $configuration,
            'File (' . $file->getName() . ') already exists in database.'
          );
        } else {
          // Add new file to database
          $fileRepository->add($file);
          $this->flashHelper->addSuccessFlash(
            $configuration,
            'File (' . $file->getName() . ') added to database.',
            $resource
          );
        }

        if ($resource->hasFile($file)) {
          $this->flashHelper->addErrorFlash(
            $configuration,
            'File (' . $file->getName() . ') already linked to product.'
          );
        } else {
          // Add Product::File association
          /** @var ProductFileRepository $productFileRepository */
          $productFileRepository = $this->container->get(
            'app.repository.product_file'
          );
          $productFile = new ProductFile();
          $productFile->setProduct($resource);
          $productFile->setEnabled(true);
          $productFile->setOwner($resource);
          $productFile->setFile($file);
          $productFileRepository->add($productFile);
          $this->flashHelper->addSuccessFlash(
            $configuration,
            'File (' . $file->getName() . ') successfully linked to product.',
            $resource
          );
        }
      }
    }
  }

  public function updateAction(Request $request): Response {
    $configuration = $this->requestConfigurationFactory->create(
      $this->metadata,
      $request
    );

    $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
    /** @var Product $resource */
    $resource = $this->findOr404($configuration);

    $form = $this->resourceFormFactory->create($configuration, $resource);

    if (
      in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) &&
      $form->handleRequest($request)
           ->isValid()
    ) {
      $this->handleFiles($resource, $request, $configuration);

      $resource = $form->getData();

      /** @var ResourceControllerEvent $event */
      $event = $this->eventDispatcher->dispatchPreEvent(
        ResourceActions::UPDATE,
        $configuration,
        $resource
      );

      if ($event->isStopped() && !$configuration->isHtmlRequest()) {
        throw new HttpException($event->getErrorCode(), $event->getMessage());
      }
      if ($event->isStopped()) {
        $this->flashHelper->addFlashFromEvent($configuration, $event);

        $eventResponse = $event->getResponse();
        if (null !== $eventResponse) {
          return $eventResponse;
        }

        return $this->redirectHandler->redirectToResource(
          $configuration,
          $resource
        );
      }

      try {
        $this->resourceUpdateHandler->handle(
          $resource,
          $configuration,
          $this->manager
        );
      } catch (UpdateHandlingException $exception) {
        if (!$configuration->isHtmlRequest()) {
          return $this->viewHandler->handle(
            $configuration,
            View::create($form, $exception->getApiResponseCode())
          );
        }

        $this->flashHelper->addErrorFlash(
          $configuration,
          $exception->getFlash()
        );

        return $this->redirectHandler->redirectToReferer($configuration);
      }

      if ($configuration->isHtmlRequest()) {
        $this->flashHelper->addSuccessFlash(
          $configuration,
          ResourceActions::UPDATE,
          $resource
        );
      }

      $postEvent = $this->eventDispatcher->dispatchPostEvent(
        ResourceActions::UPDATE,
        $configuration,
        $resource
      );

      if (!$configuration->isHtmlRequest()) {
        $view = $configuration->getParameters()
                              ->get('return_content', false)
          ? View::create($resource, Response::HTTP_OK)
          : View::create(null, Response::HTTP_NO_CONTENT);

        return $this->viewHandler->handle($configuration, $view);
      }

      $postEventResponse = $postEvent->getResponse();
      if (null !== $postEventResponse) {
        return $postEventResponse;
      }

      return $this->redirectHandler->redirectToResource(
        $configuration,
        $resource
      );
    }

    if (!$configuration->isHtmlRequest()) {
      return $this->viewHandler->handle(
        $configuration,
        View::create($form, Response::HTTP_BAD_REQUEST)
      );
    }

    $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(
      ResourceActions::UPDATE,
      $configuration,
      $resource
    );
    $initializeEventResponse = $initializeEvent->getResponse();
    if (null !== $initializeEventResponse) {
      return $initializeEventResponse;
    }

    $view = View::create()
                ->setData([
                  'configuration'            => $configuration,
                  'metadata'                 => $this->metadata,
                  'resource'                 => $resource,
                  $this->metadata->getName() => $resource,
                  'form'                     => $form->createView(),
                ])
                ->setTemplate(
                  $configuration->getTemplate(ResourceActions::UPDATE . '.html')
                );

    return $this->viewHandler->handle($configuration, $view);
  }
}
