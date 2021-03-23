<?php

namespace App\Service\Importer\Normalizer\v1;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;
use App\Service\StringNormalizer;

/**
 * Class CategoryNormalizer
 *
 * Normalizes data from Raritan V1 MySQL database.
 *
 * @package App\Service\Importer\Normalizer\v1
 */
class CategoryNormalizer implements AbstractNormalizerInterface
{
  public function normalizeEntity($item): array
  {
    $data = [
      'category_id' => $item['catid'],
      'category_type' => 'category',
      'code' => StringNormalizer::toSnake('category-' . $item['pcategoryurl']),
      'enabled' => boolval(
        is_null($item['catstatus']) ? false : $item['catstatus']
      ),
      'name' => StringNormalizer::toTitle($item['pcategoryname']),
      'slug' => StringNormalizer::toSlug($item['pcategoryurl']),
      'timestamp' => time(),
      'parent' => [
        'category_id' => 999,
        'category_type' => 'menu',
      ],
    ];
    if (array_key_exists('pcategorydescription', $item)) {
      $data['description'] = $item['pcategorydescription'];
    }

    return $data;
  }
}
