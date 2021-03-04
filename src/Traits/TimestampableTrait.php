<?php

declare(strict_types=1);

namespace App\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
  /**
   * @ORM\Column(type="datetime", name="created_at", options={"default": "CURRENT_TIMESTAMP"})
   * @var DateTimeInterface|null
   */
  protected $createdAt;

  /**
   * @ORM\Column(type="datetime", name="updated_at", options={"default": "CURRENT_TIMESTAMP"})
   * @var DateTimeInterface|null
   */
  protected $updatedAt;

  public function getCreatedAt(): ?DateTimeInterface
  {
    return $this->createdAt;
  }

  public function setCreatedAt(?DateTimeInterface $createdAt): void
  {
    $this->createdAt = $createdAt;
  }

  public function getUpdatedAt(): ?DateTimeInterface
  {
    return $this->updatedAt;
  }

  public function setUpdatedAt(?DateTimeInterface $updatedAt): void
  {
    $this->updatedAt = $updatedAt;
  }

  /**
   * @ORM\PrePersist
   */
  public function updateCreatedAt()
  {
    $this->createdAt = new DateTime('now');
  }

  /**
   * @ORM\PreUpdate
   */
  public function updateUpdatedAt()
  {
    $this->updatedAt = new DateTime('now');
  }
}
