<?php

namespace App\Service;

use App\Entity\Channel\ChannelPricing;
use App\Entity\Product\ProductAttribute;
use App\Entity\Product\ProductImage;
use App\Entity\Product\ProductOption;
use Exception;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\AttributeRepository;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductManager
{
  /**
   * @var AttributeRepository
   */
  private $attributeRepository;
  private $productFactoryInterface;
  private $productRepository;
  private $productVariantFactoryInterface;
  private $productVariantRepository;
  /**
   * @var ContainerInterface
   */
  private $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;

    $this->attributeRepository = $this->container->get(
      'sylius.repository.product_attribute'
    );
    $this->productFactoryInterface = $this->container->get(
      'sylius.factory.product'
    );
    $this->productRepository = $this->container->get(
      'sylius.repository.product'
    );
    $this->productVariantFactoryInterface = $this->container->get(
      'sylius.factory.product_variant'
    );
    $this->productVariantRepository = $this->container->get(
      'sylius.repository.product_variant'
    );
  }

  public function createOption() {
    $productOption = new ProductOption();
  }

  public function createAttribute($name, $type = 'text') {
    $productAttribute = new ProductAttribute();
    $productAttribute->setCode(StringNormalizer::toSnake($name)); // foo_bar
    $productAttribute->setName(StringNormalizer::toTitle($name)); // Foo Bar
    $productAttribute->setType($type);

    $this->attributeRepository->add($productAttribute);
  }

  /**
   * Create product from data object.
   *
   * @param $data
   *
   * @return null
   */
  public function createProduct($data) {
    try {
      /** @var ProductInterface $productInterface */
      $productInterface = $this->productFactoryInterface->createNew();

      if (
        $this->productRepository->findOneByCode($data->code) ||
        $this->productRepository->findBySlug($data->slug)
      ) {
        Logger::print(
          'Unable to import duplicate product: ' . json_encode($data)
        );
      } else {
        $productInterface->setCode($data->code);
        $productInterface->setDescription($data->description);
        $productInterface->setEnabled($data->enabled);
        $productInterface->setName($data->name);
        $productInterface->setMetaDescription($data->meta_description);
        $productInterface->setMetaKeywords($data->meta_keywords);
        $productInterface->setSlug($data->slug);

        $this->productRepository->add($productInterface);

        /** @var ProductVariantInterface $productVariantInterface */
        $productVariantInterface = $this->productVariantFactoryInterface->createForProduct(
          $productInterface
        );

        $productVariantInterface->setCode($data->code);
        $productVariantInterface->setEnabled($data->enabled);
        $productVariantInterface->setName($data->name);

        // Set pricing
        $channelPricing = new ChannelPricing();
        $channelPricing->setProductVariant($productVariantInterface);
        $channelPricing->setChannelCode($data->channel_code);
        $channelPricing->setPrice(round($data->price * 100));
        $productVariantInterface->addChannelPricing($channelPricing);

        // Images
        // TODO: Add defaults
        // TODO: Add image processing?
        if ($data->images) {
          foreach ($data->images as $image) {
            $productImage = new ProductImage();
            // Owner is base Product
            $productImage->setOwner($productInterface);
            $productImage->setPath($image->path);
            $productImage->setType($image->type);
          }
        }

        // Add variant
        $this->productVariantRepository->add($productVariantInterface);
        return $data;
      }
    } catch (Exception $e) {
      Logger::print('ERROR: ' . $e->getMessage());
    }
    return null;
  }

  /**
   * Import collection of products.
   *
   * @param array[object] $collection
   */
  public function import($collection) {
    foreach ($collection as $item) {
      $product = $this->createProduct($item);
      if ($product) {
        Logger::print('Imported: ' . json_encode($product));

        $result = $this->productRepository->find('id');
      }
    }
  }
}
