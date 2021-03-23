<?php

namespace App\Command\Import;

use App\Repository\ProductRepository;
use App\Service\Importer\ProductVariantImporter;
use App\Service\Importer\Provider\JsonProviderInterface;
use App\Service\Importer\Provider\SqlProviderInterface;
use App\Service\Logger;
use App\Service\ProductManager;
use Exception;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductVariantImportCommand extends AbstractImportCommand
{
  protected static $defaultName = 'import:product-variant';

  public function __construct(?string $name, ProductVariantImporter $importer)
  {
    $this->importer = $importer;
    parent::__construct($name);
  }

  protected function configure()
  {
    parent::configure();

    $this->setDescription('Imports a collection of product variants.')->setHelp(
      'This command imports a collection of product variants.'
    );

    $this->addOption(
      'product',
      null,
      InputOption::VALUE_OPTIONAL,
      'Defines the parent product id.',
      null
    );

    $this->addOption(
      'reset-options',
      'rs',
      InputOption::VALUE_OPTIONAL,
      'Should options be reset for parent product.',
      false
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

    $importer = $this->getImporter();
    $provider = $importer->getProvider();

    if ($provider instanceof JsonProviderInterface) {
      $importer->setNormalizer(
        new \App\Service\Importer\Normalizer\v2\ProductVariantNormalizer()
      );
    } elseif ($provider instanceof SqlProviderInterface) {
      $importer->setNormalizer(
        new \App\Service\Importer\Normalizer\v1\ProductVariantNormalizer()
      );
    }

    $product = null;
    $productCode = $input->getOption('product');
    /** @var ProductRepository $productRepository */
    $productRepository = $this->getContainer()->get(
      'sylius.repository.product'
    );
    /** @var ProductManager $productManager */
    $productManager = $this->getContainer()->get('sylius.manager.product');
    if ($productCode) {
      $product = $productRepository->findOneByCode($productCode);
    }

    // Check if options should be reset on parent product
    if (!!$input->getOption('reset-options') && $product) {
      foreach ($product->getOptions() as $option) {
        $product->removeOption($option);
      }
      $productManager->persist($product);
      $productManager->flush();
    }

    $data = $importer->execute();

    $mappedData = $importer->map($data);
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
      $importer->fromData($data, $productCode);
    }
    $productVariantManager = $this->get('sylius.manager.product_variant');
    $productVariantManager->flush();

    Logger::print('Modified ' . count($mappedData) . ' entities.');

    // Save to file
    $importer->save($mappedData);

    // Should return exit status code
    return 0;
  }

  public function validateSchema($data)
  {
    $schema = Schema::import('schema/product-collection.json');
    return $schema->in($data);
  }
}
