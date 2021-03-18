<?php

namespace App\Service\Exporter;

use App\Entity\Channel\Channel;

interface AbstractExporterInterface
{
  public function getModelName(): string;

  public function setModelName(string $modelName): void;

  public function getChannel(): Channel;

  public function setChannel($channel): void;

  public function export($id);
}
