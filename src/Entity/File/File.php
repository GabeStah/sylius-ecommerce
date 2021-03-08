<?php

declare(strict_types=1);

namespace App\Entity\File;

use App\Entity\Product\Product;
use App\Entity\User\AdminUser;
use App\Traits\TimestampableTrait;
use App\Traits\VersionableTrait;
use App\Validator as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="sylius_file")
 * @UniqueEntity(fields={"checksum"}, message="Checksum already exists.")
 * @UniqueEntity(fields={"path"}, message="Path already exists.")
 */
class File implements FileInterface, ResourceInterface
{
  use TimestampableTrait, VersionableTrait;

  const DEFAULT_TYPE = 'unknown';

  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @var int|null
   */
  protected $id;

  /**
   * @ORM\Column(type="string", unique=true, nullable=true)
   * @AppAssert\UniqueChecksum
   * @var string|null
   */
  protected $checksum;

  /**
   * @ORM\Column(name="created_by", type="integer", nullable=true)
   * @ORM\ManyToOne(targetEntity="App\Entity\User\AdminUser")
   * @ORM\JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
   * @var AdminUser|null
   */
  protected $createdBy;

  /** @var \Symfony\Component\HttpFoundation\File\File|null */
  protected $file;

  /**
   * @ORM\Column(type="string", nullable=true)
   * @var string|null
   */
  protected $name;

  /**
   * @ORM\Column(type="string", unique=true, nullable=true)
   * @var string|null
   */
  protected $path;

  /**
   * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductFile", mappedBy="file")
   * @var Product[]|null
   */
  protected $products;

  /**
   * @ORM\Column(type="integer")
   * @var int
   */
  protected $size;

  /**
   * @ORM\Column(type="string", nullable=true)
   * @var string|null
   */
  protected $title;

  /**
   * @ORM\Column(type="string")
   * @var string|null
   */
  protected $type;

  /**
   * @ORM\Column(type="string", nullable=true)
   * @var string|null
   */
  protected $url;

  /**
   * @return int|null
   */
  public function getId(): ?int {
    return $this->id;
  }

  /**
   * @return string|null
   */
  public function getChecksum(): ?string {
    return $this->checksum;
  }

  /**
   * @param string|null $checksum
   */
  public function setChecksum(?string $checksum): void {
    $this->checksum = $checksum;
  }

  public function getType(): ?string {
    return $this->type;
  }

  public function setType(?string $type): void {
    $this->type = $type;
  }

  public function hasType(): bool {
    return null !== $this->type;
  }

  /**
   * @return string|null
   */
  public function getUrl(): ?string {
    return $this->url;
  }

  /**
   * @param string|null $url
   */
  public function setUrl(?string $url): void {
    $this->url = $url;
  }

  /**
   * @return bool
   */
  public function hasUrl(): bool {
    return null !== $this->url;
  }

  /**
   * @return int|null
   */
  public function getSize(): ?int {
    return $this->size;
  }

  /**
   * @param int|null $size
   */
  public function setSize(?int $size): void {
    $this->size = $size;
  }

  /**
   * @return AdminUser|null
   */
  public function getCreatedBy(): ?AdminUser {
    return $this->createdBy;
  }

  /**
   * @param AdminUser|null $createdBy
   */
  public function setCreatedBy(?AdminUser $createdBy): void {
    $this->createdBy = $createdBy;
  }

  public function getName(): ?string {
    return $this->name;
  }

  public function setName(?string $name) {
    $this->name = $name;
  }

  /**
   * Hydrates entity based on underlying UploadedFile object.
   */
  public function hydrate() {
    if (!$this->hasFile()) {
      return;
    }

    $this->setName($this->getFile() ? $this->getFile()
                                           ->getFilename() : null);
    $this->setType($this->getFile()
                        ->getExtension());
    $this->setPath($this->getFile()
                        ->getPathname());
    $this->setVersion($this->getVersion() + 1);

    $this->updateChecksum();
    $this->updateCreatedAt();
    $this->updateSize();
    $this->updateUpdatedAt();
  }

  public function hasFile(): bool {
    if (null === $this->file && $this->hasPath()) {
      $this->updateFile();
    }

    return null !== $this->file;
  }

  public function hasPath(): bool {
    return null !== $this->path;
  }

  public function updateFile() {
    if ($this->getPath()) {
      $this->file = new \Symfony\Component\HttpFoundation\File\File(
        $this->getPath()
      );
    }
  }

  public function getPath(): ?string {
    return $this->path;
  }

  public function setPath(?string $path): void {
    $this->path = $path;
  }

  public function getFile(): ?\Symfony\Component\HttpFoundation\File\File {
    return $this->file;
  }

  public function setFile(
    ?\Symfony\Component\HttpFoundation\File\File $file
  ): void {
    $this->file = $file;
  }

  /**
   * @ORM\PrePersist
   * @ORM\PreUpdate
   */
  public function updateChecksum() {
    if ($this->hasFile()) {
      $path = $this->getFile()
                   ->getPathname();
      $result = hash_file('sha256', $path);
      $this->setChecksum($result);
    }
  }

  /**
   * @ORM\PrePersist
   * @ORM\PreUpdate
   */
  public function updateSize() {
    if ($this->hasFile()) {
      $size = $this->getFile()
                   ->getSize();
      $this->setSize($size);
    }
  }

  /**
   * @return string|null
   */
  public function getTitle(): ?string {
    if ($this->hasTitle()) {
      return $this->title;
    }

    return $this->getName();
  }

  /**
   * @param string|null $title
   */
  public function setTitle(?string $title): void {
    $this->title = $title;
  }

  /**
   * @return bool
   */
  public function hasTitle(): bool {
    return $this->title !== null && $this->title !== '';
  }
}
