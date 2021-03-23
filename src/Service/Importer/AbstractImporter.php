<?php

namespace App\Service\Importer;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;
use App\Service\Importer\Traits\NormalizerAwareTrait;
use App\Service\Importer\Traits\ProviderAwareTrait;
use App\Utility\JsonUtility;
use Doctrine\DBAL\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Imports entities from Raritan-v1 database.
 *
 * Class BaseImporter
 *
 * @package App\Service\Importers
 */
abstract class AbstractImporter implements AbstractImporterInterface
{
  use NormalizerAwareTrait, ProviderAwareTrait;

  protected $channel;

  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var string
   */
  protected $modelName = 'abstract-importer';

  /**
   * @var AbstractNormalizerInterface
   */
  protected $normalizer;

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
   * BaseConverter constructor.
   *
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
   * Execute importer.
   *
   * @return mixed
   */
  public function execute()
  {
    return $this->getProvider()->getData();
  }

  /**
   * Add extra data prior to import.
   *
   * @return mixed
   */
  public function extra()
  {
    return [];
  }

  /**
   * Filter data prior to import.
   *
   * @param $data
   *
   * @return mixed
   */
  public function filter($data)
  {
    return $data;
  }

  /**
   * Map execution results to array.
   *
   * @param mixed $data
   *
   * @return array|array[]
   */
  public function map($data)
  {
    return $this->filter(
      array_merge(
        array_map(function ($item) {
          // Backwards compatibility for importers without an explicit normalizer
          return $this->hasNormalizer()
            ? $this->getNormalizer()->normalizeEntity($item)
            : $item;
        }, $data),
        $this->extra()
      )
    );
  }

  /**
   * Save to JSON.
   *
   * @throws Exception
   * @throws \Doctrine\DBAL\Driver\Exception
   */
  public function save($data)
  {
    JsonUtility::write('exports/' . $this->getModelName() . '.json', $data);
  }

  /**
   * @return string
   */
  public function getLocale()
  {
    return $this->container->get('sylius.context.locale')->getLocaleCode();
  }

  /**
   * @return mixed
   */
  public function getChannel()
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
