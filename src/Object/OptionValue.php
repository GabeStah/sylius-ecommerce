<?php

namespace App\Object;

use App\Service\StringNormalizer;
use JsonSerializable;
use Sylius\Component\Attribute\AttributeType\CheckboxAttributeType;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;

class OptionValue implements JsonSerializable
{
  public const CONVERSION_MAP = [
    [
      'key' => 'volt',
      'code' => 'voltage',
      'name' => 'Voltage',
      'type' => TextAttributeType::TYPE,
    ],
    [
      'key' => 'pcolor',
      'code' => 'color',
      'name' => 'Color',
      'type' => TextAttributeType::TYPE,
    ],
    [
      'key' => 'psize',
      'code' => 'size',
      'name' => 'Size',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'pdegree',
      'code' => 'degree',
      'name' => 'Degree',
      'type' => TextAttributeType::TYPE,
    ],
    [
      'key' => 'ppack',
      'code' => 'pack',
      'name' => 'Pack',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'pfitting',
      'code' => 'fitting',
      'name' => 'Fitting',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'watersource',
      'code' => 'water_source',
      'name' => 'Water Source',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'control',
      'code' => 'control',
      'name' => 'Control',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'BowlBack',
      'code' => 'bowl_back',
      'name' => 'Bowl Back',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'BowlSize',
      'code' => 'bowl_size',
      'name' => 'Bowl Size',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'waterline',
      'code' => 'waterline',
      'name' => 'Waterline',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'treatment',
      'code' => 'treatment',
      'name' => 'Treatment',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'hntoption',
      'code' => 'hnt',
      'name' => 'HNT',
      'type' => CheckboxAttributeType::TYPE,
    ],
    [
      'key' => 'dischargeconversion',
      'code' => 'discharge_conversion',
      'name' => 'Discharge Conversion',
      'type' => CheckboxAttributeType::TYPE,
    ],
  ];

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
