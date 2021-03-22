<?php

namespace App\Service\Importer\Normalizer\v1;

use App\Object\Option as OptionObject;
use App\Service\Importer\Normalizer\ProductVariantNormalizerInterface;
use App\Service\StringNormalizer;

/**
 * Class ProductVariantNormalizer
 *
 * Normalizes data from Raritan V1 MySQL database.
 *
 * @package App\Service\Importer\Normalizer\v1
 */
class ProductVariantNormalizer extends ProductNormalizer implements
  ProductVariantNormalizerInterface
{
  /**
   * Normalize and map product variant data.
   *
   * @param mixed $item
   *
   * @return array
   */
  public function normalizeEntity($item): array
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
    $this->normalizeDescription($data, $item);
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
