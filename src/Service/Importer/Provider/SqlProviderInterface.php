<?php

namespace App\Service\Importer\Provider;

interface SqlProviderInterface extends AbstractProviderInterface
{
  public function connect();
  public function getQuery();
  public function setQuery(string $query);
  public function query(string $query = null, $params = null);
}
