<?php

namespace App\Command\Import;

use App\Service\Importer\ProductImporter;
use App\Service\Importer\ProductVariantImporter;
use App\Service\Logger;
use App\Service\StringNormalizer;
use Exception;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductVariantImportCommand extends AbstractImportCommand
{
  protected static $defaultName = 'import:product-variant';
  private $productManager;
  private $importer;

  public function __construct(?string $name, ProductVariantImporter $importer)
  {
    $this->importer = $importer;
    parent::__construct($name);
  }

  protected function configure()
  {
    $this->setDescription('Imports a collection of product variants.')
      ->setHelp('This command imports a collection of product variants.')
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
      if ($key % 20 == 0) {
        $this->getContainer()
          ->get('sylius.manager.product')
          ->clear();
        $this->getContainer()
          ->get('sylius.manager.product_variant')
          ->clear();
        $this->getContainer()
          ->get('sylius.manager.product_option')
          ->clear();
        $this->getContainer()
          ->get('sylius.manager.product_option_value')
          ->clear();
      }
      Logger::print('Processing ' . $data['sku']);
      $this->importer->fromData($data);
    }
    $productManager = $this->get('sylius.manager.product_variant');
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
