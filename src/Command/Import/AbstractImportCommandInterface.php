<?php

namespace App\Command\Import;

use App\Service\Importer\AbstractImporterInterface;

interface AbstractImportCommandInterface
{
  //  public function __construct(?string $name, BaseImporterInterface $importer);

  public function log($value);

  public function validateSchema($data);
}
