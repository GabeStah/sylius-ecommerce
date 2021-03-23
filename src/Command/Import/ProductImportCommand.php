<?php

namespace App\Command\Import;

use App\Service\Importer\Normalizer\v1\ProductNormalizer;
use App\Service\Importer\ProductImporter;
use App\Service\Importer\Provider\JsonProviderInterface;
use App\Service\Importer\Provider\SqlProvider;
use App\Service\Importer\Provider\SqlProviderInterface;
use App\Service\Logger;
use Exception;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImportCommand extends AbstractImportCommand
{
  protected static $defaultName = 'import:product';
  private $productManager;

  public function __construct(?string $name, ProductImporter $importer)
  {
    $this->importer = $importer;
    parent::__construct($name);
  }

  protected function configure()
  {
    $this->setDescription('Imports a collection of products.')->setHelp(
      'This command imports a collection of products.'
    );

    $this->importer->setNormalizer(new ProductNormalizer());
    $this->importer->setProvider(new SqlProvider());
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
        new \App\Service\Importer\Normalizer\v2\ProductNormalizer()
      );
    } elseif ($provider instanceof SqlProviderInterface) {
      $importer->setNormalizer(
        new \App\Service\Importer\Normalizer\v1\ProductNormalizer()
      );
    }

    $mappedData = $importer->map($importer->execute());
    foreach ($mappedData as $key => $data) {
      $this->log($data);
      $this->importer->fromData($data);
    }
    $productManager = $this->get('sylius.manager.product');
    $productManager->flush();

    Logger::print('Modified ' . count($mappedData) . ' entities.');

    // Save to file
    $importer->save();

    // Should return exit status code
    return 0;
  }

  public function validateSchema($data)
  {
    $schema = Schema::import('schema/product-collection.json');
    return $schema->in($data);
  }
}
