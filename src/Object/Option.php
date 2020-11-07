<?php

namespace App\Object;

use App\Service\StringNormalizer;
use JsonSerializable;
use Sylius\Component\Attribute\AttributeType\CheckboxAttributeType;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;

class Option implements JsonSerializable
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
  /*
   * @var string
   */
  private $storageType;
  /**
   * @var string
   */
  private $type;
  /**
   * @var OptionValue
   */
  private $optionValue;
  private $value;
  private $valueCode;

  public function __construct($code, $value)
  {
    $this->setCode(
      $this->findMapElementBy('key', $code)
        ? $this->findMapElementBy('key', $code)['code']
        : $code
    );
    $this->setName(
      $this->findMapElementBy('key', $code)
        ? $this->findMapElementBy('key', $code)['name']
        : $code
    );
    $this->setValue($value);
    $this->setOptionValue(
      new OptionValue($this->getCode(), $this->getName(), $this->getValue())
    );
  }

  /**
   * Find a conversion map element.
   *
   * @param $key
   * @param $value
   *
   * @return array|null
   */
  private function findMapElementBy($key, $value)
  {
    foreach (self::CONVERSION_MAP as $element) {
      if ($element[$key] == $value) {
        return $element;
      }
    }
    return null;
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

  /**
   * @return mixed
   */
  public function getCode()
  {
    return StringNormalizer::toSnake($this->code);
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

  public function toObject()
  {
    return [
      'code' => $this->getCode(),
      'name' => $this->getName(),
      'option_value' => $this->getOptionValue(),
      'value' => $this->getValue(),
    ];
  }

  /**
   * @return mixed
   */
  public function getValueCode()
  {
    return $this->valueCode;
  }

  /**
   * @param mixed $valueCode
   */
  public function setValueCode($valueCode): void
  {
    $this->valueCode = $valueCode;
  }

  /**
   * @return OptionValue
   */
  public function getOptionValue(): OptionValue
  {
    return $this->optionValue;
  }

  /**
   * @param OptionValue $optionValue
   */
  public function setOptionValue(OptionValue $optionValue): void
  {
    $this->optionValue = $optionValue;
  }
}
