<?php

namespace App\Object;

use App\Service\StringNormalizer;
use JsonSerializable;

class OptionValue implements JsonSerializable
{
  private $code;
  private $name;
  private $value;

  public function __construct($code, $name, $value)
  {
    $this->setCode($code);
    $this->setName($name);
    $this->setValue($value);
  }

  /**
   * @return mixed
   */
  public function getCode()
  {
    return StringNormalizer::toSnake($this->code . '_' . $this->getValue());
  }

  /**
   * @param mixed $code
   */
  public function setCode($code): void
  {
    $this->code = $code;
  }

  /**
   * @return mixed
   */
  public function getName()
  {
    return StringNormalizer::toTitle($this->name);
  }

  /**
   * @param mixed $name
   */
  public function setName($name): void
  {
    $this->name = $name;
  }

  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @param mixed $value
   */
  public function setValue($value): void
  {
    $this->value = $value;
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

  public function toObject()
  {
    return [
      'code' => $this->getCode(),
      'name' => $this->getName(),
      'value' => $this->getValue(),
    ];
  }
}
