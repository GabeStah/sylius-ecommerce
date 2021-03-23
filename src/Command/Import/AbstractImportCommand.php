<?php

namespace App\Command\Import;

use App\Service\Importer\Provider\JsonProvider;
use App\Service\Importer\Provider\SqlProvider;
use App\Service\Importer\Traits\ImporterAwareTrait;
use App\Service\Logger;
use Exception;
use Swaggest\JsonSchema\Schema;
use Sylius\Bundle\CoreBundle\Command\AbstractInstallCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractImportCommand extends AbstractInstallCommand implements
  AbstractImportCommandInterface
{
  use ImporterAwareTrait;

  protected static $defaultName = 'import:name';
  private $logging = false;

  protected $sqlConnectionParams = [];
  /**
   * @var string
   */
  protected $providerType;

  protected $queryString;

  protected const PROVIDER_TYPE_JSON = 'json';
  protected const PROVIDER_TYPE_SQL = 'sql';

  public function __construct(?string $name)
  {
    parent::__construct($name);
    $this->addOption(
      'log',
      'l',
      InputOption::VALUE_OPTIONAL,
      'Logs output.',
      false
    );

    $this->addOption(
      'path',
      null,
      InputOption::VALUE_OPTIONAL,
      'Path to source file.'
    );

    $this->addOption(
      'provider',
      null,
      InputOption::VALUE_OPTIONAL,
      'Defines the data provider.',
      static::PROVIDER_TYPE_SQL
    );
  }

  /**
   * @return bool
   */
  public function isLogging(): bool
  {
    return $this->logging;
  }

  /**
   * @param bool $logging
   */
  public function setLogging(bool $logging): void
  {
    $this->logging = $logging;
  }

  protected function configure()
  {
    $this->setDescription('Imports data.');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->setLogging($input->getOption('log') !== false);
    $output->writeln(['==================', get_class(), '==================']);

    $this->setProviderType($input->getOption('provider'));
    $output->writeln(['Provider Type: ' . strtoupper($this->providerType)]);

    $this->configureProvider($input, $output);

    // Should return exit status code
    return 0;
  }

  private function setProviderType(string $providerType)
  {
    $this->providerType = $providerType;
  }

  private function configureProvider(
    InputInterface $input,
    OutputInterface $output
  ) {
    if ($this->providerType === static::PROVIDER_TYPE_SQL) {
      $this->setSqlConnectionParams([
        'dbname' => $this->getContainer()->getParameter(
          'raritan.database.name'
        ),
        'user' => $this->getContainer()->getParameter('raritan.database.user'),
        'password' => $this->getContainer()->getParameter(
          'raritan.database.password'
        ),
        'host' => $this->getContainer()->getParameter('raritan.database.host'),
        'port' => $this->getContainer()->getParameter('raritan.database.port'),
        'driver' => 'pdo_mysql',
      ]);

      $provider = new SqlProvider();
      $provider->setOption('connection', $this->getSqlConnectionParams());
      $sqlFile = $input->getOption('path');
      if (!$sqlFile) {
        throw new Exception('Invalid SQL file path provided, aborting.');
      }
      $provider->setQuery(file_get_contents($sqlFile));
      $provider->connect();
      $this->getImporter()->setProvider($provider);
    } elseif ($this->providerType === static::PROVIDER_TYPE_JSON) {
      $provider = new JsonProvider();
      $path = $input->getOption('path');
      if (!$path) {
        throw new Exception('Path value invalid, aborting.');
      }
      $provider->setOption('path', $path);
      $this->getImporter()->setProvider($provider);
    }
  }

  public function log($value)
  {
    if ($this->isLogging()) {
      Logger::print($value);
    }
  }

  public function validateSchema($data)
  {
    $schema = Schema::import('schema/product-collection.json');
    return $schema->in($data);
  }

  /**
   * @param array|null $params
   *
   * @return $this
   */
  public function setSqlConnectionParams(?array $params): AbstractImportCommand
  {
    $this->sqlConnectionParams = $params;
    return $this;
  }

  /**
   * @return array
   */
  public function getSqlConnectionParams(): array
  {
    return $this->sqlConnectionParams;
  }
}
