<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\File\FileAwareInterface;
use App\Entity\File\FileInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface ProductFileInterface extends FileAwareInterface, ResourceInterface
{
  /**
   * @return int|null
   */
  public function getId(): ?int;

  /**
   * @return object|null
   */
  public function getOwner(): ?object;

  /**
   * @param object|null $owner
   */
  public function setOwner(?object $owner): void;

  /**
   * @return bool
   */
  public function hasOwner(): bool;

  /**
   * @return FileInterface|null
   */
  public function getFile(): ?FileInterface;

  /**
   * @param FileInterface|null $file
   */
  public function setFile(?FileInterface $file): void;

  public function hasFile(): bool;

  /**
   * @return bool
   */
  public function isEnabled(): bool;

  /**
   * @param bool $enabled
   */
  public function setEnabled(bool $enabled): void;
}
