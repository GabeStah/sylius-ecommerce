<?php

namespace App\Service;

use function Symfony\Component\String\u;
use Symfony\Component\String\Slugger\AsciiSlugger;

class StringNormalizer
{
  /**
   * Slugify value.
   *
   * @param $value
   *
   * @return string
   */
  public static function toSlug($value)
  {
    return (new AsciiSlugger())->slug($value)->toString();
  }

  /**
   * Snake-case value.
   *
   * @param      $value
   *
   * @param bool $underscorePrefix Inserts underscore before numeric values.
   *
   * @return string
   */
  public static function toSnake($value, $underscorePrefix = false)
  {
    if ($underscorePrefix) {
      return u($value)
        ->snake()
        ->replaceMatches('/([^\d])(\d++)/', '\1_\2')
        ->toString();
    }
    return u($value)
      ->snake()
      ->toString();
  }

  /**
   * Titleize value.
   *
   * @param      $value
   * @param bool $allWords
   *
   * @return string
   */
  public static function toTitle($value, $allWords = true)
  {
    return u($value)
      ->title($allWords)
      ->toString();
  }
}
