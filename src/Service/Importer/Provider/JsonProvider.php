<?php

namespace App\Service\Importer\Provider;

use App\Service\Importer\Traits\ProviderOptionsAwareTrait;
use App\Utility\JsonUtility;

class JsonProvider implements JsonProviderInterface
{
  use ProviderOptionsAwareTrait;

  /**
   * @param array|null $params
   */
  public function configure(?array $params)
  {
    $this->setOption('path', $params['path']);
  }

  public function getData()
  {
    return JsonUtility::read($this->getOption('path'));
  }
}
