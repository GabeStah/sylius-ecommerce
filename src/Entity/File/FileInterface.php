<?php

declare(strict_types=1);

namespace App\Entity\File;

use Symfony\Component\HttpFoundation\File\File;

interface FileInterface
{
  public function getChecksum(): ?string;

  public function setChecksum(?string $checksum): void;

  public function getFile(): ?File;

  public function setFile(?File $file): void;

  public function hasFile(): bool;

  public function hasPath(): bool;

  public function hydrate();

  public function getId(): ?int;

  public function getPath(): ?string;

  public function setPath(?string $path): void;

  public function getTitle(): ?string;

  public function hasTitle(): bool;

  public function setTitle(?string $title): void;

  public function getType(): ?string;

  public function setType(?string $type): void;

  public function hasType(): bool;
}
