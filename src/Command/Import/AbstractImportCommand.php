<?php

namespace App\Command\Import;

use App\Service\Importer\AbstractImporterInterface;
use App\Service\Importer\CategoryImporter;
use App\Service\Logger;
use App\Service\ProductManager;
use Exception;
use Swaggest\JsonSchema\Schema;
use Sylius\Bundle\CoreBundle\Command\AbstractInstallCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractImportCommand extends AbstractInstallCommand implements
  AbstractImportCommandInterface
{
  protected static $defaultName = 'import:name';
  private $importer;
  private $logging = false;

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
    // Should return exit status code
    return 0;
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
}
