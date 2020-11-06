<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ProductOptionManager
 *
 * @deprecated
 * @package App\Service
 */
class ProductOptionManager
{
  /**
   * @var ContainerInterface
   */
  private $container;

  private $originalNames = [
    'volt',
    'pcolor',
    'psize',
    'pdegree',
    'ppack',
    'pfitting',
    'watersource',
    'control',
    'BowlBack',
    'BowlSize',
    'waterline',
    'treatment',
    'hntoption',
    'dischargeconversion',
  ];

  /**
   * ProductOptionManager constructor.
   *
   * @param ContainerInterface $container
   *
   */
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  private function normalizeName($value)
  {
    switch ($value) {
      case 'pcolor':
        $value = 'color';
        break;
      case 'psize':
        $value = 'size';
        break;
      case 'pdegree':
        $value = 'degree';
        break;
      case 'ppack':
        $value = 'pack';
        break;
      case 'pfitting':
        $value = 'fitting';
        break;
      case 'watersource':
        $value = 'water-source';
        break;
      case 'BowlBack':
        $value = 'bowl-back';
        break;
      case 'BowlSize':
        $value = 'bowl-size';
        break;
      case 'hntoption':
        $value = 'hnt';
        break;
      case 'dischargeconversion':
        $value = 'discharge-conversion';
        break;
    }
    return $value;
  }

  public function formatCode($value)
  {
    return StringNormalizer::toSnake($this->normalizeName($value));
  }

  public function formatName($value)
  {
    return StringNormalizer::toTitle($this->normalizeName($value));
  }

  public function formatValue($name, $value)
  {
    // TODO
  }

  public function formatValueCode($name, $value)
  {
    return StringNormalizer::toSnake(
      $this->normalizeName($value) . '-' . $value
    );
  }

  public function isOptionValid($name, $value)
  {
    return $this->isOptionNameValid($name) && $this->isOptionValueValid($value);
  }

  public function isOptionNameValid($value)
  {
    return in_array($value, $this->originalNames);
  }

  public function isOptionValueValid($value)
  {
    if (!$value) {
      // Must be non-null, non-empty string
      return false;
    }
    return true;
  }
}
