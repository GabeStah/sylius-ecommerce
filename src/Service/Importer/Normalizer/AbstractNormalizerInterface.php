<?php

namespace App\Service\Importer\Normalizer;

interface AbstractNormalizerInterface
{
  public function normalizeEntity($item): array;
}
