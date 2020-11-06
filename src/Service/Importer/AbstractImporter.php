<?php

namespace App\Service\Importer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
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
  protected $channel;

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * @var string
   */
  protected $modelName = 'abstract-importer';

  /**
   * @var string
   */
  protected $queryString;

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
   * Get query string.
   *
   * @return string
   */
  public function getQuery()
  {
    return $this->queryString;
  }

  /**
   * Set query string.
   *
   * @param string $query
   *
   * @return AbstractImporter
   */
  public function setQuery(string $query)
  {
    $this->queryString = $query;
    return $this;
  }

  /**
   * BaseConverter constructor.
   *
   * @param ContainerInterface $container
   *
   * @throws Exception
   */
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
    $this->connect();

    $this->channel = $this->container
      ->get('sylius.repository.channel')
      ->findOneByCode('DEFAULT');
  }

  /**
   * Connect to db.
   *
   * @throws Exception
   */
  public function connect()
  {
    $connectionParams = [
      'dbname' => $this->container->getParameter('raritan.database.name'),
      'user' => $this->container->getParameter('raritan.database.user'),
      'password' => $this->container->getParameter('raritan.database.password'),
      'host' => $this->container->getParameter('raritan.database.host'),
      'port' => $this->container->getParameter('raritan.database.port'),
      'driver' => 'pdo_mysql',
    ];
    $this->connection = DriverManager::getConnection($connectionParams);
  }

  /**
   * Execute importer query.
   *
   * @return array
   * @throws \Doctrine\DBAL\Driver\Exception
   * @throws Exception
   */
  public function execute()
  {
    return $this->query($this->getQuery());
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
   * Normalize entity object.
   *
   * @param mixed $item
   *
   * @return array
   */
  public function normalizeEntity($item)
  {
    return [];
  }

  /**
   * Map execution results to array.
   *
   * @return array|array[]
   * @throws Exception
   * @throws \Doctrine\DBAL\Driver\Exception
   */
  public function map()
  {
    $data = $this->execute();
    return $this->filter(
      array_merge(
        array_map(function ($item) {
          return $this->normalizeEntity($item);
        }, $data),
        $this->extra()
      )
    );
  }

  /**
   * Execute query.
   *
   * @param string|null $query
   * @param null|array[string, string] $params
   *
   * @return array
   * @throws Exception
   * @throws \Doctrine\DBAL\Driver\Exception
   * @example
   */
  public function query(string $query = null, $params = null)
  {
    $statement = $this->connection->prepare($query ?? $this->getQuery());
    if ($params) {
      foreach ($params as $key => $value) {
        $statement->bindValue($key, $value);
      }
    }
    $statement->execute();
    return $statement->fetchAllAssociative();
  }

  /**
   * Save to JSON.
   *
   * @throws Exception
   * @throws \Doctrine\DBAL\Driver\Exception
   */
  public function save()
  {
    file_put_contents(
      'exports/' . $this->getModelName() . '.json',
      json_encode(
        $this->map(),
        \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
      )
    );
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
