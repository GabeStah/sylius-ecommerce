<?php

namespace App\Command;

use App\Service\ProductManager;
use Exception;
use Swaggest\JsonSchema\Schema;
use Sylius\Bundle\CoreBundle\Command\AbstractInstallCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductConvertCommand extends AbstractInstallCommand
{
  protected static $defaultName = 'product:convert';
  private $productManager;

  public function __construct(?string $name, ProductManager $productManager)
  {
    $this->productManager = $productManager;
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
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln([
      '================',
      'Product Importer',
      '================',
    ]);

    $path = $input->getOption('path');

    if (!$path) {
      throw new Exception("Invalid path specified: $path");
    } elseif (!file_exists($path)) {
      throw new Exception("Could not find file at path: $path");
    }

    $productCollection = $this->validateSchema(
      json_decode(file_get_contents($path))
    );

    $output->writeLn("Importing from path: $path");

    $this->productManager->import($productCollection);

    // Should return exit status code
    return 0;
  }

  protected function validateSchema($data)
  {
    $schema = Schema::import('schema/product-collection.json');
    return $schema->in($data);
  }
}
