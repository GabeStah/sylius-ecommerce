<?php

namespace App\Service\Importer\Normalizer;

interface ProductVariantNormalizerInterface extends ProductNormalizerInterface
{
  public function normalizeOptions(&$data, $item);
}
