<?php

namespace App\Service\Importer;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;
use App\Service\Importer\Provider\AbstractProviderInterface;
use Doctrine\DBAL\Exception;

interface AbstractImporterInterface
{
  /**
   * Get query string.
   *
   * @return string
   */
  public function getQuery();

  /**
   * Set query string.
   *
   * @param string $query
   *
   * @return AbstractImporter
   */
  public function setQuery(string $query);

  /**
   * Connect to db.
   *
   * @throws Exception
   */
  public function connect();

  /**
   * Execute importer query.
   *
   * @return array
   * @throws \Doctrine\DBAL\Driver\Exception
   * @throws Exception
   */
  public function execute();

  /**
   * Add extra data prior to import.
   *
   * @return mixed
   */
  public function extra();

  /**
   * Filter data prior to import.
   *
   * @param $data
   *
   * @return mixed
   */
  public function filter($data);

  /**
   * Normalize entity object.
   *
   * @param mixed $item
   *
   * @return array
   */
  public function normalizeEntity($item);

  /**
   * Map execution results to array.
   *
   * @param mixed $data
   *
   * @return array|array[]
   * @throws Exception
   * @throws \Doctrine\DBAL\Driver\Exception
   */
  public function map($data);

  /**
   * Execute query.
   *
   * @param string|null $query
   * @param null|array[string, string] $params
   *
   * @return array
   * @throws Exception
   * @throws \Doctrine\DBAL\Driver\Exception
   */
  public function query(string $query = null, $params = null);

  public function getNormalizer(): AbstractNormalizerInterface;

  public function hasNormalizer(): bool;

  public function setNormalizer(AbstractNormalizerInterface $normalizer);

  public function getProvider(): AbstractProviderInterface;

  public function hasProvider(): bool;

  public function setProvider(AbstractProviderInterface $provider);
}
