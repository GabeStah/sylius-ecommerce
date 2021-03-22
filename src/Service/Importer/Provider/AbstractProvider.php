<?php

namespace App\Service\Importer\Provider;

use App\Service\Importer\Traits\ProviderOptionsAwareTrait;

abstract class AbstractProvider implements AbstractProviderInterface
{
  use ProviderOptionsAwareTrait;

  /**
   * @return mixed
   */
  public function getData()
  {
  }
}
