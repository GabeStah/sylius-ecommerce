<?php

namespace App\Command\Import;

use App\Service\Importer\CategoryImporter;
use App\Service\Logger;
use Exception;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryImportCommand extends AbstractImportCommand
{
  protected static $defaultName = 'import:category';
  private $importer;

  public function __construct(?string $name, CategoryImporter $importer)
  {
    $this->importer = $importer;
    parent::__construct($name);
  }

  protected function configure()
  {
    $this->setDescription('Converts categories to taxons.');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void
   * @throws Exception|\Doctrine\DBAL\Driver\Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    parent::execute($input, $output);

    $mappedData = $this->importer->map();
    usort($mappedData, function ($a, $b) {
      return $a['code'] > $b['code'];
    });
    foreach ($mappedData as $key => $data) {
      $this->importer->fromData($data);
    }

    // Flush DB changes
    $manager = $this->get('sylius.manager.taxon');
    $manager->flush();

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
