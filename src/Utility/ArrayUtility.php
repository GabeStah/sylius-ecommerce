<?php

namespace App\Utility;

class ArrayUtility
{
  /**
   * @param array $array
   *
   * @return bool
   */
  public static function hasData(array $array): bool
  {
    foreach ($array as $key => $value) {
      if ($value !== null) {
        return true;
      }
    }
    return false;
  }

  /**
   * Set an array item to a given value using "dot" notation.
   *
   * If no key is given to the method, the entire array will be replaced.
   *
   * @param  array   $array
   * @param  string  $key
   * @param  mixed   $value
   * @return array
   *
   * @source https://laravel.com/docs/5.2/helpers#method-array-set
   */
  public static function set(array &$array, string $key, $value): array
  {
    if (is_null($key)) {
      return $array = $value;
    }

    $keys = explode('.', $key);

    while (count($keys) > 1) {
      $key = array_shift($keys);

      // If the key doesn't exist at this depth, we will just create an empty array
      // to hold the next value, allowing us to create the arrays to hold final
      // values at the correct depth. Then we'll keep digging into the array.
      if (!isset($array[$key]) || !is_array($array[$key])) {
        $array[$key] = [];
      }

      $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;

    return $array;
  }
}
