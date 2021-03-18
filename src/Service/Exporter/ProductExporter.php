<?php namespace App\Service\Exporter;

use App\Entity\Channel\ChannelPricing;
use App\Entity\Product\ProductOptionValue;
use App\Repository\ProductRepository;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Export products from Raritan-v2 db.
 *
 * Class ProductExporter
 *
 * @package App\Service\Exporters
 */
class ProductExporter extends AbstractExporter implements
  AbstractExporterInterface
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
   * ProductExporter constructor.
   *
   * @param ContainerInterface $container
   *
   * @param ProductRepository  $productRepository
   *
   */
  public function __construct(
    ContainerInterface $container,
    ProductRepository $productRepository
  ) {
    parent::__construct($container);
    $this->repository = $productRepository;
  }

  /**
   * @param $id
   */
  public function export($id)
  {
    $product = $this->repository->findOneByCode($id);

    file_put_contents(
      'exports/product/code-' . $id . '.json',
      json_encode(
        $this->normalizeProduct($product),
        \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
      )
    );
  }

  /**
   * Normalize a ProductInterface object to JSON-able array.
   *
   * @param ProductInterface $product
   *
   * @return array
   */
  private function normalizeProduct(ProductInterface $product): array
  {
    $variants = $product->getVariants();

    $data = [
      'id' => $product->getId(),
      'code' => $product->getCode(),
      'name' => $product->getName(),
      'description' => $product->getDescription(),
      'short_description' => $product->getShortDescription(),
      'meta_description' => $product->getMetaDescription(),
      'meta_keywords' => $product->getMetaKeywords(),
    ];

    $options = array_map(function ($option) {
      return $option->getCode();
    }, $product->getOptions()->toArray());

    sort($options);

    $data['variants'] = array_map(function ($variant) use ($options) {
      return $this->normalizeProductVariant($variant, $options);
    }, $variants->toArray());

    return $data;
  }

  /**
   * Normalize a ProductVariant to JSON-able array.
   *
   * @param ProductVariantInterface $productVariant
   * @param                         $availableOptions
   *
   * @return array
   */
  private function normalizeProductVariant(
    ProductVariantInterface $productVariant,
    $availableOptions
  ): array {
    /** @var ChannelPricing $pricing */
    $pricing = $productVariant->getChannelPricings()->get('DEFAULT');

    $data = [
      'code' => $productVariant->getCode(),
      'name' => $productVariant->getTranslation()->getName(),
      'price' => $pricing->getPrice(),
      'depth' => $productVariant->getDepth(),
      'height' => $productVariant->getHeight(),
      'width' => $productVariant->getWidth(),
      'weight' => $productVariant->getWeight(),
      'version' => $productVariant->getVersion(),
      'options' => [],
    ];

    $options = $productVariant->getOptionValues();

    foreach ($availableOptions as $availableOption) {
      $data['options'][$availableOption] = null;
    }

    /** @var ProductOptionValue $option */
    foreach ($options->toArray() as $option) {
      $data['options'][$option->getOption()->getCode()] = $option->getValue();
    }

    return $data;
  }
}
