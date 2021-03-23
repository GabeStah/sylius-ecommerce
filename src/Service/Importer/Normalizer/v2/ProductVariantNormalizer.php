<?php

namespace App\Service\Importer\Normalizer\v2;

use App\Object\Option as OptionObject;
use App\Service\Importer\Normalizer\ProductVariantNormalizerInterface;

/**
 * Class ProductVariantNormalizer
 *
 * Normalizes data from Raritan V2 JSON.
 *
 * @package App\Service\Importer\Normalizer\v2
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
      'product_id' => $item['product'],
      'options' => [],
      'price' => intval($item['price']),
      'name' => $item['name'],
      'sku' => $item['code'],
      'weight' => $item['weight'],
      'version' => $item['version'],
    ];

    $this->normalizeDimensions($data, $item);
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

    if ($item['options'] && is_array($item['options'])) {
      foreach ($item['options'] as $key => $value) {
        $options[] = new OptionObject($key, $value);
      }
    }

    $data['options'] = $options;
  }
}
