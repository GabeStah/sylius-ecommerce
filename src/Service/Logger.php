<?php

namespace App\Service;

class Logger
{
  /**
   * Print to console.
   *
   * @param $value
   */
  public static function print($value)
  {
    if (is_string($value)) {
      print_r($value . PHP_EOL);
    } else {
      print_r($value);
      print_r(PHP_EOL);
    }
  }
}
