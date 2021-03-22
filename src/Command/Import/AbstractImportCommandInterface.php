<?php

namespace App\Command\Import;

interface AbstractImportCommandInterface
{
  public function log($value);
  public function validateSchema($data);
  public function setSqlConnectionParams(?array $params): AbstractImportCommand;
  public function getSqlConnectionParams(): array;
}
