<?php

declare(strict_types=1);

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait VersionableTrait
{
  /**
   * @ORM\Column(type="integer")
   * @var int
   */
  protected $version = 0;

  /**
   * @return int
   */
  public function getVersion(): int
  {
    return $this->version;
  }

  /**
   * @param int $version
   */
  public function setVersion(int $version): void
  {
    $this->version = $version;
  }

  /**
   * @ORM\PreUpdate
   */
  public function incrementVersion()
  {
    $this->version++;
  }
}
