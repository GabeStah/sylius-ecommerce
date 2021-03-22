<?php

namespace App\Service\Importer;

use App\Entity\Channel\ChannelPricing;
use App\Entity\Product\Product;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use App\Object\Option as OptionObject;
use App\Repository\ProductRepository;
use App\Service\ProductOptionManager;
use Doctrine\DBAL\Exception;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductOptionRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Bundle\TaxationBundle\Doctrine\ORM\TaxCategoryRepository;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import product variants from Raritan-v1 db.
 *
 * Class ProductVariantImporter
 *
 * @package App\Service\Importers
 */
class ProductVariantImporter extends ProductImporter
{
  /**
   * @var string
   */
  protected $modelName = 'product-variant';

  /**
   * @var ProductRepository
   */
  private $repository;
  /**
   * @var TaxonRepository
   */
  private $taxonRepository;
  /**
   * @var ProductVariantRepository
   */
  private $productVariantRepository;
  /**
   * @var ProductOptionRepository
   */
  private $productOptionRepository;
  /**
   * @var ProductOptionManager
   */
  private $productOptionManager;
  /**
   * @var object|EntityRepository|null
   */
  private $channelPricingRepository;
  /**
   * @var object|EntityRepository|null
   */
  private $productOptionValueRepository;
  /**
   * @var TaxCategoryRepository
   */
  private $taxCategoryRepository;

  /**
   * ProductImporter constructor.
   *
   * @param ContainerInterface       $container
   *
   * @param ProductRepository        $productRepository
   *
   * @param TaxonRepository          $taxonRepository
   *
   * @param ProductVariantRepository $productVariantRepository
   *
   * @param ProductOptionRepository  $productOptionRepository
   *
   * @param ProductOptionManager     $productOptionManager
   *
   * @throws Exception
   */
  public function __construct(
    ContainerInterface $container,
    ProductRepository $productRepository,
    TaxonRepository $taxonRepository,
    ProductVariantRepository $productVariantRepository,
    ProductOptionRepository $productOptionRepository,
    ProductOptionManager $productOptionManager
  ) {
    parent::__construct($container, $productRepository, $taxonRepository);
    $this->repository = $productRepository;
    $this->taxonRepository = $taxonRepository;
    $this->productVariantRepository = $productVariantRepository;
    $this->productOptionRepository = $productOptionRepository;
    $this->productOptionManager = $productOptionManager;
    $this->channelPricingRepository = $this->container->get(
      'sylius.repository.channel_pricing'
    );
    $this->productOptionValueRepository = $this->container->get(
      'sylius.repository.product_option_value'
    );
    $this->taxCategoryRepository = $this->container->get(
      'sylius.repository.tax_category'
    );
  }

  /**
   * Filter product variant list.
   *
   * @param $data
   *
   * @return array|mixed
   */
  public function filter($data)
  {
    $invalidProductIds = [
      1126,
      1127,
      1128,
      1129,
      1130,
      1131,
      1132,
      1133,
      1287,
      1288,
      1298,
      1299,
      1300,
      1308,
      1309,
      1316,
      1317,
      1318,
      1319,
      1320,
    ];

    return array_filter($data, function ($item) use ($invalidProductIds) {
      if (
        (array_key_exists('prid', $item) &&
          in_array($item['prid'], $invalidProductIds)) ||
        !$item['sku']
      ) {
        return false;
      }
      return true;
    });
  }

  /**
   * Create ProductVariant and related entities from model.
   *
   * @param $data
   *
   * @return ProductVariant
   */
  public function fromData($data): ProductVariant
  {
    // Find base product
    $product = $this->repository->findOneByCode($data['product_id']);

    // Find existing product variant or create new instance
    $entity =
      $this->productVariantRepository->findOneByCodeAndProductCode(
        $data['sku'],
        $data['product_id']
      ) ?? new ProductVariant();

    $entity->setCurrentLocale($this->getLocale());
    $entity->setCode($data['sku']);
    $entity->setEnabled(
      array_key_exists('enabled', $data) && is_bool($data['enabled'])
        ? $data['enabled']
        : true
    );
    $entity->setName($data['name']);
    $entity->setProduct($product);
    $entity->setDepth($data['depth']);
    $entity->setHeight($data['height']);
    $entity->setTaxCategory(
      $this->taxCategoryRepository->findOneBy(['code' => 'taxable_good'])
    );
    $entity->setWidth($data['width']);
    $entity->setWeight($data['weight']);

    // Add variant to base product
    $product->addVariant($entity);

    $manager = $this->container->get('sylius.manager.product_variant');
    $manager->persist($entity);
    $manager->flush();

    // Save ProductVariant
    $this->productVariantRepository->add($entity);

    // Find existing channel pricing or create new instance
    $pricing =
      $this->channelPricingRepository->findOneBy([
        'productVariant' => $entity->getId(),
      ]) ?? new ChannelPricing();

    $pricing->setProductVariant($entity);
    $pricing->setPrice($data['price']);
    $pricing->setChannelCode($this->getChannel()->getCode());

    // Save ChannelPricing
    $this->channelPricingRepository->add($pricing);
    $manager = $this->container->get('sylius.manager.channel_pricing');
    $manager->persist($pricing);

    $this->generateOptions($entity, $product, $data);

    return $entity;
  }

  /**
   * Generate product variant options from data.
   *
   * @param ProductVariant           $entity
   * @param Product|ProductInterface $product
   * @param                          $data
   */
  public function generateOptions(
    ProductVariant $entity,
    Product $product,
    $data
  ) {
    if (!$data['options'] || count($data['options']) === 0) {
      return;
    }

    /** @var ProductOptionValueInterface $optionValue */
    foreach ($entity->getOptionValues() as $optionValue) {
      $entity->removeOptionValue($optionValue);
    }

    /** @var OptionObject $optionObject */
    foreach ($data['options'] as $optionObject) {
      $option =
        $this->productOptionRepository->findOneBy([
          'code' => $optionObject->getCode(),
        ]) ?? new ProductOption();
      $option->setCurrentLocale($this->getLocale());
      $option->setCode($optionObject->getCode());
      $option->setName($optionObject->getName());
      $this->productOptionRepository->add($option);
      $optionValue =
        $this->productOptionValueRepository->findOneBy([
          'option' => $option->getId(),
          'code' => $optionObject->getOptionValue()->getCode(),
        ]) ?? new ProductOptionValue();
      $optionValue->setCode($optionObject->getOptionValue()->getCode());
      $optionValue->setCurrentLocale($this->getLocale());
      $optionValue->setValue($optionObject->getOptionValue()->getValue());
      $option->addValue($optionValue);

      $this->container
        ->get('sylius.manager.product_option_value')
        ->persist($optionValue);

      // Add OptionValue to product
      $entity->addOptionValue($optionValue);

      // Add option to product
      $product->addOption($option);
    }
  }
}
