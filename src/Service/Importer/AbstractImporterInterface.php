<?php

namespace App\Service\Importer;

use App\Service\Importer\Normalizer\AbstractNormalizerInterface;
use App\Service\Importer\Provider\AbstractProviderInterface;
use Doctrine\DBAL\Exception;

interface AbstractImporterInterface
{
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
   * Map execution results to array.
   *
   * @param mixed $data
   *
   * @return array|array[]
   * @throws Exception
   * @throws \Doctrine\DBAL\Driver\Exception
   */
  public function map($data);

  public function getNormalizer(): AbstractNormalizerInterface;

  public function hasNormalizer(): bool;

  public function setNormalizer(AbstractNormalizerInterface $normalizer);

  public function getProvider(): AbstractProviderInterface;

  public function hasProvider(): bool;

  public function setProvider(AbstractProviderInterface $provider);
}
