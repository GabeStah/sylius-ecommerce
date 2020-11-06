<?php

namespace App\Object;

use JsonSerializable;
use Sylius\Component\Attribute\AttributeType\CheckboxAttributeType;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;

class Attribute implements JsonSerializable
{
  public const CONVERSION_MAP = [
    [
      'key' => 'upccode',
      'code' => 'upc',
      'name' => 'Universal Product Code',
      'type' => TextAttributeType::TYPE,
    ],
    [
      'key' => 'producttype',
      'code' => 'type',
      'name' => 'Product Type',
      'type' => TextAttributeType::TYPE,
    ],
    [
      'key' => 'pdualvoltage',
      'code' => 'dual_voltage',
      'name' => 'Dual Voltage',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'tdesign',
      'code' => 't_design',
      'name' => 'T-Design',
      'type' => TextAttributeType::TYPE,
    ],
    [
      'key' => 'frontpage',
      'code' => 'front_page',
      'name' => 'Front Page',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'cancerharm',
      'code' => 'cancer_harm',
      'name' => 'Cancer Harm',
      'type' => CheckboxAttributeType::TYPE,
    ],
  ];

  private $code;
  private $name;
  /*
   * @var string
   */
  private $storageType;
  /**
   * @var string
   */
  private $type;
  private $value;

  public function __construct(
    $code,
    $name,
    $value,
    $type = TextAttributeType::TYPE
  ) {
    $this->setCode($code);
    $this->setName($name);
    $this->setType($type);
    $this->setValue($value);
  }

  /**
   * @return mixed
   */
  public function getCode()
  {
    return $this->code;
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
    return $this->name;
  }

  /**
   * @param mixed $name
   */
  public function setName($name): void
  {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getType(): string
  {
    return $this->type;
  }

  /**
   * @param string $type
   */
  public function setType(string $type): void
  {
    $this->type = $type;
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
      'storage_type' => $this->getStorageType(),
      'type' => $this->getType(),
      'value' => $this->getValue(),
    ];
  }

  /**
   * @return mixed
   */
  public function getStorageType()
  {
    if ($this->storageType) {
      return $this->storageType;
    }
    switch ($this->getType()) {
      case CheckboxAttributeType::TYPE:
        return 'boolean';
      case TextAttributeType::TYPE:
      default:
        return 'text';
    }
  }

  /**
   * @param mixed $storageType
   */
  public function setStorageType($storageType): void
  {
    $this->storageType = $storageType;
  }
}
