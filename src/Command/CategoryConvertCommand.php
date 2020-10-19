<?php

namespace App\Command;

use App\Service\Converters\CategoryConverter;
use App\Service\Logger;
use App\Service\ProductManager;
use Exception;
use Swaggest\JsonSchema\Schema;
use Sylius\Bundle\CoreBundle\Command\AbstractInstallCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryConvertCommand extends AbstractInstallCommand
{
  protected static $defaultName = 'product:category:convert';
  private $converter;

  public function __construct(?string $name, CategoryConverter $converter)
  {
    $this->converter = $converter;
    parent::__construct($name);
  }

  protected function configure()
  {
    $this->setDescription('Converts categories to taxons.');
    //      ->setDefinition(
    //        new InputDefinition([
    //          new InputOption(
    //            'path',
    //            'p',
    //            InputOption::VALUE_REQUIRED,
    //            'Path to JSON product data'
    //          ),
    //        ])
    //      );
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
      '==================',
      'Category Converter',
      '==================',
    ]);

    $outputData = $this->converter->map();
    Logger::print($outputData);

    //    $productCollection = $this->validateSchema(
    //      json_decode(file_get_contents($path))
    //    );
    //
    //    $output->writeLn("Importing from path: $path");
    //
    //    $this->productManager->import($productCollection);

    // Should return exit status code
    return 0;
  }

  protected function validateSchema($data)
  {
    $schema = Schema::import('schema/product-collection.json');
    return $schema->in($data);
  }
}
