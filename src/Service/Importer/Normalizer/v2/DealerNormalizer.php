<?php

namespace App\Service\Importer\Normalizer\v2;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;

/**
 * Class DealerNormalizer
 *
 * Normalizes data from Raritan V2 JSON.
 *
 * @package App\Service\Importer\Normalizer\v2
 */
class DealerNormalizer implements AbstractNormalizerInterface
{
  public function normalizeEntity($item): array
  {
    return $item;
  }
}
