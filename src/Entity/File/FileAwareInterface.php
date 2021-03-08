<?php

declare(strict_types=1);

namespace App\Entity\File;

interface FileAwareInterface
{
  public function getFile(): ?FileInterface;

  public function hasFile(): bool;

  public function setFile(?FileInterface $file): void;
}
