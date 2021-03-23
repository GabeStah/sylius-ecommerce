<?php

namespace App\Service\Importer;

use App\Service\StringNormalizer;

/**
 * Import subcategories.
 *
 * Class SubcategoryImporter
 *
 * @package App\Service\Importers
 */
class SubcategoryImporter extends CategoryImporter implements
  AbstractImporterInterface
{
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
}
