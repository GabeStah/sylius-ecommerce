<?php

namespace App\Service\Importer\Normalizer\v1;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;
use App\Service\StringNormalizer;

/**
 * Class DealerNormalizer
 *
 * Normalizes data from Raritan V1 MySQL database.
 *
 * @package App\Service\Importer\Normalizer\v1
 */
class DealerNormalizer implements AbstractNormalizerInterface
{
  public function normalizeEntity($item): array
  {
    return [
      'name' => $item['dealername'],
      'slug' => StringNormalizer::toSlug($item['dealerurl']),
      'address1' => $item['daddress1'],
      'address2' => $item['daddress2'],
      'city' => $item['dcity'],
      'postal_code' => $item['dzipcode'],
      'state' => $item['dstate'],
      'country' => $item['dcountry'],
      'phone' => $item['dphone'],
      'fax' => $item['dfax'],
      'email' => $item['demail'],
      'url' => $item['dwebsite'],
      'enabled' => boolval(
        is_null($item['dstatus']) ? false : $item['dstatus']
      ),
      'type' => $item['dealertype'],
      'wholesale' => boolval(
        is_null($item['wholesaleonly']) ? false : $item['wholesaleonly']
      ),
      'comment' => $item['dcomment'],
      'latitude' => $item['gmaplat'],
      'longitude' => $item['gmaplong'],
    ];
  }
}
