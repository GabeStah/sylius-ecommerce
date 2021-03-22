<?php

namespace App\Service\Importer\Normalizer\v1;

use App\Object\Attribute as AttributeObject;
use App\Object\ProductImage as ProductImageObject;
use App\Service\Importer\Normalizer\ProductNormalizerInterface;
use App\Service\StringNormalizer;
use Sylius\Component\Attribute\AttributeType\CheckboxAttributeType;

/**
 * Class ProductNormalizer
 *
 * Normalizes data from Raritan V1 MySQL database.
 *
 * @package App\Service\Importer\Normalizer\v1
 */
class ProductNormalizer implements ProductNormalizerInterface
{
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
   * Normalize and map product variant data.
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
        return '/en_US/product-categories/';
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
}
