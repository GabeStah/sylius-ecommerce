<?php

namespace App\Service\Converters;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseConverter
{
  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var ContainerInterface
   */
  protected $container;

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

    //    $this->attributeRepository = $this->container->get(
    //      'sylius.repository.product_attribute'
    //    );

    $this->connect();
  }

  /**
   * Connect to db.
   *
   * @throws Exception
   */
  protected function connect()
  {
    $connectionParams = [
      'dbname' => 'raritane_ncustom',
      'user' => 'root',
      'password' => 'example',
      'host' => 'localhost',
      'port' => 3309,
      'driver' => 'pdo_mysql',
    ];
    $this->connection = DriverManager::getConnection($connectionParams);
  }

  /**
   * Execute query.
   *
   * @param string $query
   * @param null|array[string, string] $params
   *
   * @return array
   * @throws Exception|\Doctrine\DBAL\Driver\Exception
   */
  public function query(string $query, $params = null)
  {
    //    $sql = "SELECT * FROM users WHERE name = :name OR username = :name";
    $statement = $this->connection->prepare($query);
    if ($params) {
      foreach ($params as $key => $value) {
        $statement->bindValue($key, $value);
      }
    }
    $statement->execute();
    return $statement->fetchAllAssociative();
  }
}
