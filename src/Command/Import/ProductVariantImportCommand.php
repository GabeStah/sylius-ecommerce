<?php

namespace App\Command\Import;

use App\Service\Importer\ProductVariantImporter;
use App\Service\Importer\Provider\JsonProviderInterface;
use App\Service\Importer\Provider\SqlProviderInterface;
use App\Service\Logger;
use Exception;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductVariantImportCommand extends AbstractImportCommand
{
  protected static $defaultName = 'import:product-variant';
  private $productManager;
  /**
   * Base retrieval query string.
   *
   * Retrieves all enabled products that are NOT 'trudesign'
   *
   * @var string
   */
  protected $queryString = <<<EOF
SELECT
  p.*,
  s1.subcatid subcatid1,
  s1.subcatname subcatname1,
  s1.subcaturl subcaturl1,
  s2.subcatid subcatid2,
  s2.subcatname subcatname2,
  s2.subcaturl subcaturl2,
  s3.subcatid subcatid3,
  s3.subcatname subcatname3,
  s3.subcaturl subcaturl3,
  s4.subcatid subcatid4,
  s4.subcatname subcatname4,
  s4.subcaturl subcaturl4
FROM
  products p
LEFT JOIN
  productsubcategory s1
  ON
    p.psubcategory = s1.subcatid
LEFT JOIN
  productsubcategory s2
  ON
    p.psubcategory3 = s2.subcatid
LEFT JOIN
  productsubcategory s3
  ON
    p.psubcategory4 = s3.subcatid
LEFT JOIN
  productsubcategory s4
  ON
    p.psubcategory5 = s4.subcatid
WHERE
  seourl NOT LIKE '%trudesign%'
AND
  prstatus = 1
ORDER BY
  productnumber ASC
EOF;

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

    // TODO: Get product_id from command option if not in data
    // Use optional command option to delete all existing ProductOptions before variant additions
    // Get product entity and pass to fromData() method to reduce lookups

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
      $importer->fromData($data);
    }
    $productManager = $this->get('sylius.manager.product_variant');
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
