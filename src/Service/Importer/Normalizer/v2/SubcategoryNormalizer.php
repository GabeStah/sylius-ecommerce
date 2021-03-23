<?php

namespace App\Service\Importer\Normalizer\v2;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;

/**
 * Class SubcategoryNormalizer
 *
 * Normalizes data from Raritan V2 JSON.
 *
 * @package App\Service\Importer\Normalizer\v2
 */
class SubcategoryNormalizer implements AbstractNormalizerInterface
{
  public function normalizeEntity($item): array
  {
    return $item;
  }
}
