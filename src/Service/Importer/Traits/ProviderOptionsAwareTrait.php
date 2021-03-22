<?php

namespace App\Service\Importer\Traits;

trait ProviderOptionsAwareTrait
{
  /**
   * @var array
   */
  protected $options = [];

  /**
   * @return array
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @param array|null $options
   *
   * @return $this
   */
  public function setOptions(?array $options): self
  {
    $this->options = $options;
    return $this;
  }

  /**
   * @param string $key
   *
   * @return mixed|null
   */
  public function getOption(string $key)
  {
    if (array_key_exists($key, $this->options)) {
      return $this->options[$key];
    }
    return null;
  }

  /**
   * @param string $key
   * @param        $value
   *
   * @return $this
   */
  public function setOption(string $key, $value): self
  {
    $this->options[$key] = $value;
    return $this;
  }
}
