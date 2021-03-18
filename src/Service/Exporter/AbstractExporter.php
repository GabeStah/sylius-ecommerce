<?php

namespace App\Service\Exporter;

use App\Entity\Channel\Channel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Exports entities.
 *
 * Class AbstractExporter
 *
 * @package App\Service\Exporters
 */
abstract class AbstractExporter implements AbstractExporterInterface
{
  /**
   * @var Channel
   */
  protected $channel;

  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var string
   */
  protected $modelName = 'abstract-exporter';

  /**
   * @var string
   */
  protected $queryString;

  /**
   * @param ContainerInterface $container
   *
   */
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;

    $this->channel = $this->container
      ->get('sylius.repository.channel')
      ->findOneByCode('DEFAULT');
  }

  /**
   * @return string
   */
  public function getModelName(): string
  {
    return $this->modelName;
  }

  /**
   * @param string $modelName
   */
  public function setModelName(string $modelName): void
  {
    $this->modelName = $modelName;
  }

  /**
   * @return mixed
   */
  public function getChannel(): Channel
  {
    return $this->channel;
  }

  /**
   * @param mixed $channel
   */
  public function setChannel($channel): void
  {
    $this->channel = $channel;
  }
}
