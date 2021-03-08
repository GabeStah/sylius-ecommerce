<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\File\FileInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Sylius\Component\Core\Model\ProductInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_file",uniqueConstraints={@UniqueConstraint(name="owner_id_file_id", columns={"owner_id", "file_id"})})
 */
class ProductFile implements ProductFileInterface
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @ORM\Column(type="boolean")
   * @var bool
   */
  protected $enabled = true;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\File\File", inversedBy="products", cascade="persist")
   * @ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="CASCADE")
   * @var FileInterface|null
   */
  protected $file;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\Product\Product", inversedBy="files", cascade="persist")
   * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
   * @var ProductInterface|null
   */
  protected $owner;
  /**
   * @var ProductInterface|null
   */
  protected $product;

  /**
   * @return int|null
   */
  public function getId(): ?int
  {
    return $this->id;
  }

  /**
   * @return object|null
   */
  public function getOwner(): ?object
  {
    return $this->owner;
  }

  /**
   * @param object|null $owner
   */
  public function setOwner(?object $owner): void
  {
    $this->owner = $owner;
  }

  /**
   * @return bool
   */
  public function hasOwner(): bool
  {
    return null !== $this->owner;
  }

  public function getProduct(): ?ProductInterface
  {
    return $this->product;
  }

  public function setProduct(?ProductInterface $product): void
  {
    $this->product = $product;
  }

  /**
   * @return FileInterface|null
   */
  public function getFile(): ?FileInterface
  {
    return $this->file;
  }

  /**
   * @param FileInterface|null $file
   */
  public function setFile(?FileInterface $file): void
  {
    $this->file = $file;
  }

  public function hasFile(): bool
  {
    return null !== $this->file;
  }

  /**
   * @return bool
   */
  public function isEnabled(): bool
  {
    return $this->enabled;
  }

  /**
   * @param bool $enabled
   */
  public function setEnabled(bool $enabled): void
  {
    $this->enabled = $enabled;
  }
}
