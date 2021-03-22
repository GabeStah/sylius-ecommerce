<?php

namespace App\Service\Importer\Traits;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;

trait NormalizerAwareTrait
{
  /**
   * @var AbstractNormalizerInterface
   */
  protected $normalizer;

  public function getNormalizer(): AbstractNormalizerInterface
  {
    return $this->normalizer;
  }

  public function hasNormalizer(): bool
  {
    return $this->normalizer instanceof AbstractNormalizerInterface;
  }

  public function setNormalizer(AbstractNormalizerInterface $normalizer)
  {
    $this->normalizer = $normalizer;
  }
}
