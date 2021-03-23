<?php namespace App\Service\Importer;

use App\Entity\Product\Product;
use App\Entity\Product\ProductAttribute;
use App\Entity\Product\ProductAttributeValue;
use App\Entity\Product\ProductImage;
use App\Object\Attribute as AttributeObject;
use App\Object\ProductImage as ProductImageObject;
use App\Repository\ProductRepository;
use App\Service\Logger;
use Doctrine\DBAL\Exception;
use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductAttributeValueRepository;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
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
   * Filter product variant list.
   *
   * @param $data
   *
   * @return array|mixed
   */
  public function filter($data)
  {
    $invalidProductIds = [1126, 1127, 1128, 1129, 1130, 1131, 1132, 1133];

    return array_filter($data, function ($item) use ($invalidProductIds) {
      if (key_exists('prid', $item)) {
        if (in_array($item['prid'], $invalidProductIds) || !$item['sku']) {
          return false;
        }
      }
      return true;
    });
  }

  /**
   * Create Entity from data.
   *
   * @param                       $data
   * @param string|null           $productCode
   *
   * @return Product
   */
  public function fromData($data, ?string $productCode)
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

  public function findCategoryTaxon(
    $id,
    $url = null,
    $type = 'subcategory'
  ): ?TaxonInterface {
    return $this->taxonRepository->findOneByCategory($id, $type) ??
      $this->taxonRepository->findOneBySlug($url, $this->getLocale());
  }
}
