<?php

namespace App\Utility;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class JsonUtility
{
  /**
   * Read data from a JSON file.
   *
   * @param            $path
   * @param false      $associative
   * @param int|string $flags
   *
   * @return mixed
   */
  public static function read(
    $path,
    $associative = true,
    $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
  ) {
    return json_decode(file_get_contents($path), $associative, 512, $flags);
  }

  /**
   * Write data to JSON file.
   *
   * @param            $path
   * @param            $data
   * @param int        $flags
   */
  public static function write(
    $path,
    $data,
    $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
  ) {
    file_put_contents($path, json_encode($data, $flags));
  }
}
