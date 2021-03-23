<?php

namespace App\Service\Importer\Normalizer;

interface ProductNormalizerInterface extends AbstractNormalizerInterface
{
  public function normalizeAttributes(&$data, $item);
  public function normalizeCategories(&$data, $item);
  public function normalizeDescription(&$data, $item);
  public function normalizeDimensions(&$data, $item);
  public function normalizeEntity($item): array;
  public function normalizeImages(&$data, $item);
  public function normalizeMeta(&$data, $item);
}
