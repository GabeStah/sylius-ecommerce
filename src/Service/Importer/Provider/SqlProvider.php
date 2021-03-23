<?php

namespace App\Service\Importer\Provider;

use App\Service\Importer\Traits\ProviderOptionsAwareTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class SqlProvider implements SqlProviderInterface
{
  use ProviderOptionsAwareTrait;

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var string
   */
  protected $queryString;

  /**
   * @return array|\Doctrine\DBAL\Driver\Exception|Exception|\Exception
   */
  public function getData()
  {
    try {
      return $this->query($this->getQuery());
    } catch (\Doctrine\DBAL\Driver\Exception | Exception $e) {
      return $e;
    }
  }

  /**
   * Get query string.
   *
   * @return string
   */
  public function getQuery(): string
  {
    return $this->queryString;
  }

  /**
   * Set query string.
   *
   * @param string $query
   *
   * @return SqlProvider
   */
  public function setQuery(string $query): SqlProvider
  {
    $this->queryString = $query;
    return $this;
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
  public function query(string $query = null, $params = null): array
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

  public function connect()
  {
    $this->connection = DriverManager::getConnection(
      $this->getOption('connection')
    );
  }
}
