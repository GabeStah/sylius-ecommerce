<?php

namespace App\Service\Importer\Traits;

use App\Service\Importer\AbstractImporterInterface;

trait ImporterAwareTrait
{
  /**
   * @var AbstractImporterInterface
   */
  protected $importer;

  public function getImporter(): AbstractImporterInterface
  {
    return $this->importer;
  }

  public function hasImporter(): bool
  {
    return $this->importer instanceof AbstractImporterInterface;
  }

  public function setImporter(AbstractImporterInterface $importer)
  {
    $this->importer = $importer;
  }
}
