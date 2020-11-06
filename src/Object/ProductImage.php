<?php

namespace App\Object;

use JsonSerializable;
use SplFileInfo;

class ProductImage implements JsonSerializable
{
  private $path;
  private $type;
  private $file;
  private $owner;

  public function __construct($path, $type = 'main', $file = null)
  {
    $this->setPath($path);
    $this->setType($type);
    $this->setFile($file ?? new SplFileInfo($path));
  }

  /**
   * @return mixed
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * @param mixed $path
   */
  public function setPath($path): void
  {
    $this->path = $path;
  }

  /**
   * @return mixed
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @param mixed $type
   */
  public function setType($type): void
  {
    $this->type = $type;
  }

  /**
   * @return SplFileInfo|null
   */
  public function getFile(): ?SplFileInfo
  {
    return $this->file;
  }

  /**
   * @param SplFileInfo|null $file
   */
  public function setFile(?SplFileInfo $file): void
  {
    $this->file = $file;
  }

  /**
   * @return mixed
   */
  public function getOwner()
  {
    return $this->owner;
  }

  /**
   * @param mixed $owner
   */
  public function setOwner($owner): void
  {
    $this->owner = $owner;
  }

  /**
   * Specify data which should be serialized to JSON
   *
   * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
   * @return mixed data which can be serialized by <b>json_encode</b>,
   * which is a value of any type other than a resource.
   * @since 5.4
   */
  public function jsonSerialize()
  {
    return get_object_vars($this);
  }
}
