<?php

namespace App\Service\Importer\Normalizer\v1;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;
use App\Service\StringNormalizer;

/**
 * Class SubcategoryNormalizer
 *
 * Normalizes data from Raritan V1 MySQL database.
 *
 * @package App\Service\Importer\Normalizer\v1
 */
class SubcategoryNormalizer implements AbstractNormalizerInterface
{
  public function normalizeEntity($item): array
  {
    $code = StringNormalizer::toSnake('subcategory-' . $item['subcaturl']);

    $data = [
      'category_id' => $item['subcatid'],
      'category_type' => 'subcategory',
      'code' => $code,
      'enabled' => true,
      'name' => StringNormalizer::toTitle($item['subcatname']),
      'slug' => StringNormalizer::toSlug($item['subcaturl']),
      'timestamp' => time(),
    ];

    if ($item['parent4']) {
      $data['parent'] = [
        'category_id' => $item['parent4'],
        'category_type' => 'subcategory',
      ];
    } elseif ($item['parent3']) {
      $data['parent'] = [
        'category_id' => $item['parent3'],
        'category_type' => 'subcategory',
      ];
    } elseif ($item['parent2']) {
      $data['parent'] = [
        'category_id' => $item['parent2'],
        'category_type' => 'subcategory',
      ];
    } else {
      $data['parent'] = [
        'category_id' => $item['categoryid'],
        'category_type' => 'category',
      ];
    }

    if (array_key_exists('subcatdescription', $item)) {
      $data['description'] = $item['subcatdescription'];
    }
    return $data;
  }
}
