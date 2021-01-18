<?php namespace App\Service\Importer;

use App\Entity\Product\Product;
use App\Entity\Product\ProductAttribute;
use App\Entity\Product\ProductAttributeValue;
use App\Entity\Product\ProductImage;
use App\Object\Attribute as AttributeObject;
use App\Object\ProductImage as ProductImageObject;
use App\Repository\ProductRepository;
use App\Service\Logger;
use App\Service\StringNormalizer;
use Doctrine\DBAL\Exception;
use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductAttributeValueRepository;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Sylius\Component\Attribute\AttributeType\CheckboxAttributeType;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import products from Raritan-v1 db.
 *
 * Class ProductImporter
 *
 * @package App\Service\Importers
 */
class ProductImporter extends AbstractImporter implements
  AbstractImporterInterface
{
  /**
   * @var string
   */
  protected $modelName = 'product';

  /**
   * Base retrieval query string.
   *
   * Gets all products that are not 'trudesign'.
   * Joins all subcategory relationship data.
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
  (
    SELECT
      p.productnumber,
      MAX(p.prid) AS prid
    FROM
      products p
    GROUP BY
      p.productnumber
  ) p_max
INNER JOIN
  products p
  ON
    p_max.productnumber = p.productnumber
  AND
    p_max.prid = p.prid
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
   * @var object|EntityRepository|null
   */
  private $productAttributeRepository;
  /**
   * @var object|ProductAttributeValueRepository|null
   */
  private $productAttributeValueRepository;

  /**
   * ProductImporter constructor.
   *
   * @param ContainerInterface $container
   *
   * @param ProductRepository  $productRepository
   *
   * @param TaxonRepository    $taxonRepository
   *
   * @throws Exception
   */
  public function __construct(
    ContainerInterface $container,
    ProductRepository $productRepository,
    TaxonRepository $taxonRepository
  ) {
    parent::__construct($container);
    $this->repository = $productRepository;
    $this->taxonRepository = $taxonRepository;
    $this->productAttributeRepository = $this->container->get(
      'sylius.repository.product_attribute'
    );
    $this->productAttributeValueRepository = $this->container->get(
      'sylius.repository.product_attribute_value'
    );
  }

  /**
   * Generate product attributes based on data.
   *
   * @param Product $entity
   * @param         $data
   */
  private function generateAttributes(Product $entity, $data)
  {
    if (!isset($data['attributes'])) {
      return;
    }
    /** @var AttributeObject $attributeObject */
    foreach ($data['attributes'] as $attributeObject) {
      /** @var ProductAttribute $attribute */
      $attribute =
        $this->productAttributeRepository->findOneBy([
          'code' => $attributeObject->getCode(),
        ]) ??
        $this->container->get('sylius.factory.product_attribute')->createNew();

      $attribute->setCode($attributeObject->getCode());
      $attribute->setName($attributeObject->getName());
      $attribute->setStorageType($attributeObject->getStorageType());
      $attribute->setType($attributeObject->getType());

      /** @var ProductAttributeValue $attributeValue */
      $attributeValue =
        $this->productAttributeValueRepository->findOneBy([
          'attribute' => $attribute->getId(),
          'localeCode' => $this->getLocale(),
          'subject' => $entity->getId(),
        ]) ??
        $this->container
          ->get('sylius.factory.product_attribute_value')
          ->createNew();

      $attributeValue->setAttribute($attribute);
      $attributeValue->setLocaleCode($this->getLocale());
      $attributeValue->setProduct($entity);
      $attributeValue->setValue($attributeObject->getValue());

      $this->container
        ->get('sylius.manager.product_attribute')
        ->persist($attribute);
      $this->container->get('sylius.manager.product_attribute')->flush();

      $this->container
        ->get('sylius.manager.product_attribute_value')
        ->persist($attributeValue);
      $this->container->get('sylius.manager.product_attribute_value')->flush();
    }
  }

  /**
   * Generate product images based on data.
   *
   * @param Product $entity
   * @param         $data
   */
  private function generateImages(Product $entity, $data)
  {
    $productImageRepository = $this->container->get(
      'sylius.repository.product_image'
    );

    /** @var ProductImageObject $imageObject */
    foreach ($data['images'] as $imageObject) {
      /** @var ProductImage $image */
      $image =
        $productImageRepository->findOneBy([
          'owner' => $entity->getId(),
          'path' => $imageObject->getFile()->getPathname(),
        ]) ??
        $this->container->get('sylius.factory.product_image')->createNew();

      $image->setFile($imageObject->getFile());
      $image->setOwner($entity);
      $image->setPath($imageObject->getFile()->getPathname());
      $image->setType($imageObject->getType());

      $productImageRepository->add($image);
      $entity->addImage($image);
    }
  }

  /**
   * Generate product taxons based on data.
   *
   * @param Product $entity
   * @param         $data
   */
  private function generateTaxons(Product $entity, $data)
  {
    $taxon = $this->taxonRepository->findOneByCategory(
      $data['category']['category_id'],
      $data['category']['category_type']
    );

    if ($taxon) {
      $productTaxonRepository = $this->container->get(
        'sylius.repository.product_taxon'
      );

      $productTaxon =
        $productTaxonRepository->findOneByProductCodeAndTaxonCode(
          $data['product_id'],
          $taxon->getCode()
        ) ?? $this->container->get('sylius.factory.product_taxon')->createNew();

      $productTaxon->setTaxon($taxon);
      $productTaxon->setProduct($entity);
      // Add taxon if doesn't exist
      if (!$entity->hasProductTaxon($productTaxon)) {
        $entity->addProductTaxon($productTaxon);
      }
      // Set main taxon
      $entity->setMainTaxon($taxon);
      Logger::print(
        'Found matching parent taxon: ' .
          $data['category']['category_id'] .
          ' : ' .
          $data['category']['category_type']
      );
    } else {
      Logger::print(
        'No matching parent taxon: ' .
          $data['category']['category_id'] .
          ' : ' .
          $data['category']['category_type']
      );
    }
  }

  /**
   * Get valid slug based on data and possible entity.
   *
   * Prefixes slug with product_id if base slug already exists.
   *
   * @param Product|ProductInterface $entity
   * @param                          $data
   *
   * @return mixed|string|null
   */
  public function getSlug($entity, $data): ?string
  {
    return $entity
      ? $entity->getSlug()
      : ($this->repository->findOneByChannelAndSlug(
        $this->getChannel(),
        $this->getLocale(),
        $data['slug']
      )
        ? $data['product_id'] . '-' . $data['slug']
        : $data['slug']);
  }

  /**
   * Create Entity from data.
   *
   * @param $data
   *
   * @return Product
   */
  public function fromData($data)
  {
    $isNew = false;
    // Find by code
    $entity = $this->repository->findOneByCode($data['product_id']);
    $slug = $this->getSlug($entity, $data);

    if (!$entity) {
      $isNew = true;
      $entity = new Product();
    }

    $entity->setCurrentLocale($this->getLocale());
    $entity->setCode($data['product_id']);
    $entity->addChannel($this->getChannel());
    $entity->setDescription($data['description']);
    $entity->setEnabled(is_bool($data['enabled']) ? $data['enabled'] : true);
    $entity->setName($data['name']);
    $entity->setSlug($slug);
    $entity->setDescription($data['description']);
    $entity->setShortDescription($data['meta_description']);
    $entity->setMetaDescription($data['meta_description']);
    $entity->setMetaKeywords($data['meta_keywords']);
    $entity->setVariantSelectionMethod('match');

    if ($isNew) {
      $this->repository->add($entity);
    }

    $productManager = $this->container->get('sylius.manager.product');
    $productManager->flush();

    $this->generateAttributes($entity, $data);
    $this->generateImages($entity, $data);
    $this->generateTaxons($entity, $data);

    return $entity;
  }

  /**
   * Normalize and map entity.
   *
   * @param mixed $item
   *
   * @return array
   */
  public function normalizeEntity($item): array
  {
    $data = [
      'id' => $item['prid'],
      'category' => $this->getPrimaryCategory($item),
      'enabled' => boolval(
        is_null($item['prstatus']) ? false : $item['prstatus']
      ),
      'product_id' => $item['productnumber'],
      'images' => [],
      'price' => $item['punitprice'] ? intval($item['punitprice'] * 100) : 0,
      'name' => StringNormalizer::toTitle($item['productname']),
      'sku' => $item['partnumber'],
      'slug' => StringNormalizer::toSlug($item['seourl']),
      'timestamp' => time(),
      'weight' => $item['weight'],
    ];

    $this->normalizeAttributes($data, $item);
    $this->normalizeDimensions($data, $item);
    $this->normalizeDescription($data, $item);
    $this->normalizeImages($data, $item);
    $this->normalizeMeta($data, $item);

    return $data;
  }

  public function findCategoryTaxon(
    $id,
    $url = null,
    $type = 'subcategory'
  ): ?TaxonInterface {
    return $this->taxonRepository->findOneByCategory($id, $type) ??
      $this->taxonRepository->findOneBySlug($url, $this->getLocale());
  }

  /**
   * Get attribute value from category, if applicable.
   *
   * @param $value
   *
   * @return mixed|null
   */
  public function getCategoryAttribute($value): ?AttributeObject
  {
    if ($value == 'part' || $value == 'parts') {
      return new AttributeObject(
        'part',
        'Part',
        true,
        CheckboxAttributeType::TYPE
      );
    }
    if ($value == 'accessory' || $value == 'accessories') {
      return new AttributeObject(
        'accessory',
        'Accessory',
        true,
        CheckboxAttributeType::TYPE
      );
    }
    return null;
  }

  /**
   * Get normalized attributes based on special-case categories.
   *
   * Raritan-v1 categories such as 'parts' and 'accessories' are discarded and
   * applicable products are assigned related attributes instead.
   *
   * @param $item
   *
   * @return AttributeObject|mixed|null
   */
  private function getAttributesFromCategories($item): ?AttributeObject
  {
    for ($i = 4; $i >= 1; $i--) {
      // Inversely iterate subcategories
      $categoryData = $this->getCategoryData($item, 'subcategory', $i);
      if ($categoryData) {
        return $this->getCategoryAttribute($categoryData['category_slug']);
      }
    }
    return null;
  }

  /**
   * Generate category data from item.
   *
   * @param        $item
   * @param string $type
   * @param int    $index
   *
   * @return array|null
   */
  private function getCategoryData(
    $item,
    $type = 'subcategory',
    $index = 1
  ): ?array {
    switch ($type) {
      case 'category':
        if (!$item['pcategory']) {
          return null;
        }
        return [
          'category_id' => $item['pcategory'],
          'category_type' => 'category',
        ];
      case 'miscellaneous':
        return [
          'category_id' => '100',
          'category_name' => 'Miscellaneous',
          'category_slug' => 'miscellaneous',
          'category_type' => 'category',
        ];
      default:
        if (!$item['subcatid' . $index]) {
          return null;
        }
        return [
          'category_id' => $item['subcatid' . $index],
          'category_name' => $item['subcatname' . $index],
          'category_slug' => $item['subcaturl' . $index],
          'category_type' => 'subcategory',
        ];
    }
  }

  /**
   * Get primary category of item.
   *
   * @param $item
   *
   * @return array|null
   */
  private function getPrimaryCategory($item): ?array
  {
    for ($i = 4; $i >= 1; $i--) {
      // Inversely iterate subcategories
      $categoryData = $this->getCategoryData($item, 'subcategory', $i);
      if (
        $categoryData &&
        $this->isValidCategorySlug($categoryData['category_slug'])
      ) {
        return $categoryData;
      }
    }

    // Get category data, otherwise miscellaneous category
    return $this->getCategoryData($item, 'category') ??
      $this->getCategoryData($item, 'miscellaneous');
  }

  /**
   * Determine if category slug value is valid.
   *
   * @param $value
   *
   * @return bool
   */
  private function isValidCategorySlug($value): bool
  {
    return !in_array($value, ['part', 'parts', 'accessory', 'accessories']);
  }

  /**
   * Normalize and map attributes.
   *
   * Attribute data stored as AttributeObjects.
   *
   * @param $data
   * @param $item
   */
  public function normalizeAttributes(&$data, $item)
  {
    $attributes = [];
    if ($this->getAttributesFromCategories($item)) {
      $attributes[] = $this->getAttributesFromCategories($item);
    }

    foreach (AttributeObject::CONVERSION_MAP as $element) {
      $value = $item[$element['key']];
      if ($value) {
        $attributes[] = new AttributeObject(
          $element['code'],
          $element['name'],
          $element['type'] === CheckboxAttributeType::TYPE
            ? boolval($value)
            : $value,
          $element['type']
        );
      }
    }

    $data['attributes'] = $attributes;
  }

  /**
   * Normalize and map categories.
   *
   * @param $data
   * @param $item
   */
  public function normalizeCategories(&$data, $item)
  {
    if ($item['subcatid4']) {
      $data['categories'][] = [
        'category_id' => $item['subcatid4'],
        'category_slug' => $item['subcaturl4'],
        'category_type' => 'subcategory',
      ];
    }
    if ($item['subcatid3']) {
      $data['categories'][] = [
        'category_id' => $item['subcatid3'],
        'category_slug' => $item['subcaturl3'],
        'category_type' => 'subcategory',
      ];
    }
    if ($item['subcatid2']) {
      $data['categories'][] = [
        'category_id' => $item['subcatid2'],
        'category_slug' => $item['subcaturl2'],
        'category_type' => 'subcategory',
      ];
    }
    if ($item['subcatid1']) {
      $data['categories'][] = [
        'category_id' => $item['subcatid1'],
        'category_slug' => $item['subcaturl1'],
        'category_type' => 'subcategory',
      ];
    }
    $data['categories'][] = [
      'category_id' => $item['pcategory'],
      'category_type' => 'category',
    ];
  }

  /**
   * Replaces invalid, static URLs with valid ones.
   *
   * @param $matches
   *
   * @return string
   */
  private function descriptionRegexCallback($matches): string
  {
    $callbacks = [
      '/\.\.\/\.\.\/en\/info\//' => function () {
        return '/en_US/page/';
      },
      '/\.\.\/\.\.\/en\/product-categories\//' => function () {
        return '/en_US/taxons/';
      },
      '/\.\.\/\.\.\/en\/shop\/products\//' => function () {
        return '/en_US/products/';
      },
      '/\.\.\/\.\.\/en\/tech-support\//' => function () {
        return '/en_US/page/';
      },
      '/\.\.\/\.\.\/fileslibrary\//' => function () {
        return '/media/image/';
      },
      '/\.\.\/\.\.\//' => function () {
        return '/';
      },
      '/http:\/\/raritaneng\.com\/en\/pages\//' => function () {
        return '/en_US/page/';
      },
    ];

    return 'href="' .
      preg_replace_callback_array($callbacks, $matches[1]) .
      '"';
  }

  /**
   * Normalize and map descriptions.
   *
   * @param $data
   * @param $item
   */
  public function normalizeDescription(&$data, $item)
  {
    $patternHref = '/href="(.+?)"/';

    $data['description'] = null;

    if ($item['gendescription']) {
      $data['description'] = preg_replace_callback(
        $patternHref,
        [$this, 'descriptionRegexCallback'],
        $item['gendescription']
      );
    }

    $data['variant_description'] = null;

    if ($item['pdescription']) {
      $data['variant_description'] = preg_replace_callback(
        $patternHref,
        [$this, 'descriptionRegexCallback'],
        $item['pdescription']
      );
    }
  }

  /**
   * Normalize and map dimensions.
   *
   * @param $data
   * @param $item
   */
  public function normalizeDimensions(&$data, $item)
  {
    $data['depth'] = $item['pdepth'];
    $data['height'] = $item['pheight'];
    $data['width'] = $item['pwidth'];
  }

  /**
   * Normalize and map images.
   *
   * Image data stored as ProductImageObjects.
   *
   * @param $data
   * @param $item
   */
  public function normalizeImages(&$data, $item)
  {
    if ($item['pmainimage']) {
      $data['images'][] = new ProductImageObject($item['pmainimage'], 'main');
    }

    if ($item['pmobileimage1']) {
      $data['images'][] = new ProductImageObject(
        $item['pmobileimage1'],
        'mobile'
      );
    }

    if ($item['p2mainimage']) {
      $data['images'][] = new ProductImageObject($item['p2mainimage'], 'main');
    }

    if ($item['pmobileimage2']) {
      $data['images'][] = new ProductImageObject(
        $item['pmobileimage2'],
        'mobile'
      );
    }

    if ($item['p3mainimage']) {
      $data['images'][] = new ProductImageObject($item['p3mainimage'], 'main');
    }

    if ($item['pmobileimage3']) {
      $data['images'][] = new ProductImageObject(
        $item['pmobileimage3'],
        'mobile'
      );
    }
  }

  /**
   * Normalize and map meta data.
   *
   * @param $data
   * @param $item
   */
  public function normalizeMeta(&$data, $item)
  {
    $data['meta_description'] = $item['metadesc'] ?? null;
    $data['meta_keywords'] = $item['metakeyword'] ?? null;
  }
}
