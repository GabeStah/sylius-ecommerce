<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\File\FileInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Product\Model\ProductTranslationInterface;
use Sylius\Component\Review\Model\ReviewInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct
{
  /**
   * Many Products have Many Files.
   * @OneToMany(targetEntity="App\Entity\Product\ProductFile", mappedBy="owner")
   *
   * @var Collection|FileInterface[]
   *
   * @psalm-var Collection<array-key, FileInterface>
   */
  public $files;

  public function __construct()
  {
    parent::__construct();

    /** @var ArrayCollection<array-key, ProductTaxonInterface> $this- >productTaxons */
    $this->productTaxons = new ArrayCollection();

    /** @var ArrayCollection<array-key, ChannelInterface> $this- >channels */
    $this->channels = new ArrayCollection();

    /** @var ArrayCollection<array-key, ReviewInterface> $this- >reviews */
    $this->reviews = new ArrayCollection();

    /** @var ArrayCollection<array-key, ImageInterface> $this- >images */
    $this->images = new ArrayCollection();

    /** @var ArrayCollection<array-key, FileInterface> $this- >files */
    $this->files = new ArrayCollection();
  }

  protected function createTranslation(): ProductTranslationInterface
  {
    return new ProductTranslation();
  }

  public function getProductFiles()
  {
    return $this->files;
  }

  public function addProductFile(ProductFileInterface $productFile): void
  {
    if (!$this->hasProductFile($productFile)) {
      $this->files->add($productFile);
      $productFile->setProduct($this);
    }
  }

  public function removeProductFile(ProductFileInterface $productFile): void
  {
    if ($this->hasProductFile($productFile)) {
      $this->files->removeElement($productFile);
    }
  }

  /**
   * @param ProductFileInterface|null $productFile
   *
   * @return bool
   */
  public function hasProductFile(?ProductFileInterface $productFile): bool
  {
    if ($productFile) {
      return $this->files->contains($productFile);
    } else {
      return $this->files->count() > 0;
    }
  }

  public function getFiles(): Collection
  {
    return $this->files->map(function (
      ProductFileInterface $productFile
    ): FileInterface {
      return $productFile->getFile();
    });
  }

  /**
   * @param FileInterface|null $file
   *
   * @return bool
   */
  public function hasFile(?FileInterface $file): bool
  {
    if ($file) {
      /** @var FileInterface $existingFile */
      foreach ($this->getFiles() as $existingFile) {
        if ($existingFile->getChecksum() === $file->getChecksum()) {
          return true;
        }
      }
    }
    return false;
  }

  public function addFile(FileInterface $file)
  {
    $this->files[] = $file;
  }

  public function removeFile(FileInterface $file)
  {
    foreach ($this->getFiles() as $key => $value) {
      if ($value->getId() === $file->getId()) {
        $this->getFiles()->remove($key);
      }
    }
  }
}
