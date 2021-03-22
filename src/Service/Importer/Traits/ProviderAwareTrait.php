<?php

namespace App\Service\Importer\Traits;

use App\Service\Importer\Provider\AbstractProviderInterface;

trait ProviderAwareTrait
{
  /**
   * @var AbstractProviderInterface
   */
  protected $provider;

  public function getProvider(): AbstractProviderInterface
  {
    return $this->provider;
  }

  public function hasProvider(): bool
  {
    return $this->provider instanceof AbstractProviderInterface;
  }

  public function setProvider(AbstractProviderInterface $provider)
  {
    $this->provider = $provider;
  }
}
