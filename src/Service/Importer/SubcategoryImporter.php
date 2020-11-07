<?php

namespace App\Service\Importer;

use App\Service\StringNormalizer;

/**
 * Import subcategories from Raritan-v1 db.
 *
 * Class SubcategoryImporter
 *
 * @package App\Service\Importers
 */
class SubcategoryImporter extends CategoryImporter implements
  AbstractImporterInterface
{
  /**
   * Base retrieval query string.
   *
   * Retrieves all subcategories that do not
   * have a 'trudesign' parent category.
   *
   * @var string
   */
  protected $queryString = <<<EOF
  SELECT
    *
  FROM
    productsubcategory
  WHERE
    categoryid !=
    (
      SELECT
        catid
      FROM
        productcategory
      WHERE
        pcategoryurl LIKE "trudesign%"
    )
    AND
    subcaturl != ''
EOF;

  protected $modelName = 'subcategory';

  /**
   * Filter subcategory list.
   *
   * Remove duplicate category pairs.
   * Remove accessories and parts (in favor of primary categories).
   *
   * @param $data
   *
   * @return array|mixed
   */
  public function filter($data)
  {
    $invalidCategoryIds = [114, 21, 152, 63];
    return array_filter($data, function ($item) use ($invalidCategoryIds) {
      if (in_array($item['category_id'], $invalidCategoryIds)) {
        return false;
      }
      if (
        $item['slug'] == 'accessories' ||
        $item['slug'] == 'parts' ||
        $item['slug'] == 'part'
      ) {
        return false;
      }
      return true;
    });
  }

  /**
   * Normalize and map entity data.
   *
   * @param mixed $item
   *
   * @return array
   */
  public function normalizeEntity($item)
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
