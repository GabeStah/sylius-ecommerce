<?php

namespace App\Command\Import;

use App\Service\Importer\ProductImporter;
use App\Service\Logger;
use Exception;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImportCommand extends AbstractImportCommand
{
  protected static $defaultName = 'import:product';
  private $productManager;
  private $importer;

  public function __construct(?string $name, ProductImporter $importer)
  {
    $this->importer = $importer;
    parent::__construct($name);
  }

  protected function configure()
  {
    $this->setDescription('Imports a collection of products.')
      ->setHelp('This command imports a collection of products.')
      ->setDefinition(
        new InputDefinition([
          new InputOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to JSON product data'
          ),
        ])
      );
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void
   * @throws Exception
   * @throws \Doctrine\DBAL\Driver\Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    parent::execute($input, $output);

    $mappedData = $this->importer->map();
    foreach ($mappedData as $key => $data) {
      $this->log($data);
      $this->importer->fromData($data);
    }
    $productManager = $this->get('sylius.manager.product');
    $productManager->flush();

    Logger::print('Modified ' . count($mappedData) . ' entities.');

    // Save to file
    $this->importer->save();

    // Should return exit status code
    return 0;
  }

  public function validateSchema($data)
  {
    $schema = Schema::import('schema/product-collection.json');
    return $schema->in($data);
  }
}
