<?php

namespace App\Service\Importer;

use App\Entity\Channel\ChannelPricing;
use App\Entity\Product\Product;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use App\Object\Option as OptionObject;
use App\Repository\ProductRepository;
use App\Service\Logger;
use App\Service\ProductOptionManager;
use App\Service\StringNormalizer;
use Doctrine\DBAL\Exception;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductOptionRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Sylius\Component\Core\Model\ProductInterface;
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
   * Base retrieval query string.
   *
   * Retrieves all enabled products that are NOT 'trudesign'
   *
   * @var string
   */
  protected $queryString = <<<EOF
SELECT
  p.*,
  s1.subcatid subcatid1,
  s1.subcatname subcatname1,
  s1.subcaturl subcaturl1,
  s2.subcatid subcatid2,
  s2.subcatname subcatname2,
  s2.subcaturl subcaturl2,
  s3.subcatid subcatid3,
  s3.subcatname subcatname3,
  s3.subcaturl subcaturl3,
  s4.subcatid subcatid4,
  s4.subcatname subcatname4,
  s4.subcaturl subcaturl4
FROM
  products p
LEFT JOIN
  productsubcategory s1
  ON
    p.psubcategory = s1.subcatid
LEFT JOIN
  productsubcategory s2
  ON
    p.psubcategory3 = s2.subcatid
LEFT JOIN
  productsubcategory s3
  ON
    p.psubcategory4 = s3.subcatid
LEFT JOIN
  productsubcategory s4
  ON
    p.psubcategory5 = s4.subcatid
WHERE
  seourl NOT LIKE '%trudesign%'
AND
  prstatus = 1
ORDER BY
  productnumber ASC
EOF;

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
   * @var object|\Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository|null
   */
  private $channelPricingRepository;
  /**
   * @var object|\Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository|null
   */
  private $productOptionValueRepository;

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
      if (in_array($item['prid'], $invalidProductIds) || !$item['sku']) {
        return false;
      }
      return true;
    });
  }

  /**
   * Create ProductVariant and related entities from model.
   *
   * @param $data
   */
  public function fromData($data)
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
    $entity->setEnabled(is_bool($data['enabled']) ? $data['enabled'] : true);
    $entity->setName($data['name']);
    $entity->setProduct($product);
    $entity->setDepth($data['depth']);
    $entity->setHeight($data['height']);
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

  /**
   * Normalize and map product variant data.
   *
   * @param mixed $item
   *
   * @return array
   */
  public function normalizeEntity($item)
  {
    $data = [
      'prid' => $item['prid'],
      'enabled' => boolval(
        is_null($item['prstatus']) ? false : $item['prstatus']
      ),
      'product_id' => $item['productnumber'],
      'images' => [],
      'options' => [],
      'price' => $item['punitprice'] ? intval($item['punitprice'] * 100) : 0,
      'name' => StringNormalizer::toTitle($item['productname']),
      'sku' => $item['partnumber'],
      'slug' => StringNormalizer::toSlug($item['seourl']),
      'timestamp' => time(),
      'weight' => $item['weight'],
    ];

    $data['description'] = $item['gendescription'] ?? null;
    $data['variant_description'] = $item['pdescription'] ?? null;

    $this->normalizeAttributes($data, $item);
    $this->normalizeCategories($data, $item);
    $this->normalizeDimensions($data, $item);
    $this->normalizeImages($data, $item);
    $this->normalizeMeta($data, $item);
    $this->normalizeOptions($data, $item);

    return $data;
  }

  /**
   * Normalize and map product options data.
   *
   * @param $data
   * @param $item
   */
  public function normalizeOptions(&$data, $item)
  {
    $options = [];

    foreach (OptionObject::CONVERSION_MAP as $element) {
      $value = $item[$element['key']];
      if ($value) {
        $options[] = new OptionObject($element['code'], $value);
      }
    }

    $data['options'] = $options;
  }
}
