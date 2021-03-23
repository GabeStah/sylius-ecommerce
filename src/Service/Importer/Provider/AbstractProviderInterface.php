<?php

namespace App\Service\Importer\Provider;

interface AbstractProviderInterface
{
  public function getData();
  public function getOptions(): array;
  public function setOptions(?array $options);
  public function getOption(string $key);
  public function setOption(string $key, $value);
}
