<?php

namespace App\Model\Taxonomy;

use App\Service\Logger;

/**
 * Taxon model used to map between import data and Taxon Entity.
 *
 * Class Taxon
 *
 * @package App\Model\Taxonomy
 */
class Taxon
{
  /**
   * @var string
   */
  private $code;
  /**
   * @var array[string]
   */
  private $descriptions = [];
  /**
   * @var bool
   */
  private $enabled = true;
  /**
   * @var string
   */
  private $name;
  /**
   * @var bool
   */
  private $product_visible;
  /**
   * @var string
   */
  private $slug;
  /**
   * @var integer
   */
  private $timestamp;

  /**
   * @var Taxon
   */
  private $parent = null;

  /**
   * Taxon constructor.
   *
   * @param mixed $data
   */
  public function __construct($data = null)
  {
    $this->setCode($data['code']);
    $this->setDescriptions($data['descriptions'] ?? null);
    $this->setEnabled(is_bool($data['enabled']) ? $data['enabled'] : true);
    $this->setName($data['name']);
    $this->setSlug($data['slug']);
    if (array_key_exists('parent', $data)) {
      if ($data['parent'] instanceof \App\Entity\Taxonomy\Taxon) {
        $this->setParent($data['parent']);
      } else {
        $this->setParent(new self($data['parent']));
      }
    }
  }

  /**
   * @return string
   */
  public function getCode(): string
  {
    return $this->code;
  }

  /**
   * @param string $code
   *
   * @return Taxon
   */
  public function setCode(string $code): Taxon
  {
    $this->code = $code;
    return $this;
  }

  /**
   * @return array
   */
  public function getDescriptions(): array
  {
    return $this->descriptions;
  }

  /**
   * @param array|null $descriptions
   *
   * @return Taxon
   */
  public function setDescriptions(?array $descriptions): Taxon
  {
    if ($descriptions) {
      $this->descriptions = $descriptions;
    }
    return $this;
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
   *
   * @return Taxon
   */
  public function setEnabled(bool $enabled): Taxon
  {
    $this->enabled = $enabled;
    return $this;
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return Taxon
   */
  public function setName(string $name): Taxon
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getSlug(): string
  {
    return $this->slug;
  }

  /**
   * @param string $slug
   *
   * @return Taxon
   */
  public function setSlug(string $slug): Taxon
  {
    $this->slug = $slug;
    return $this;
  }

  /**
   * @return int
   */
  public function getTimestamp(): int
  {
    return $this->timestamp;
  }

  /**
   * @param int $timestamp
   *
   * @return Taxon
   */
  public function setTimestamp(int $timestamp): Taxon
  {
    $this->timestamp = $timestamp;
    return $this;
  }

  /**
   * @return null|Taxon|\App\Entity\Taxonomy\Taxon
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * @param Taxon|\App\Entity\Taxonomy\Taxon $parent
   *
   * @return Taxon
   */
  public function setParent($parent): Taxon
  {
    $this->parent = $parent;
    return $this;
  }

  /**
   * @return bool
   */
  public function isProductVisible(): ?bool
  {
    return $this->product_visible;
  }

  /**
   * @param bool $product_visible
   *
   * @return Taxon
   */
  public function setProductVisible(bool $product_visible): Taxon
  {
    $this->product_visible = $product_visible;
    return $this;
  }
}
