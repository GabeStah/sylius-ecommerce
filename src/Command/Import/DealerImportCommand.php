<?php

namespace App\Command\Import;

use App\Service\Importer\DealerImporter;
use App\Service\Logger;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DealerImportCommand extends AbstractImportCommand
{
  protected static $defaultName = 'import:dealer';
  private $importer;

  public function __construct(?string $name, DealerImporter $importer)
  {
    $this->importer = $importer;
    parent::__construct($name);
  }

  protected function configure()
  {
    $this->setDescription('Imports dealers.');
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
      return $a['slug'] > $b['slug'];
    });
    /** @var EntityManagerInterface $manager */
    $manager = $this->get('app.manager.dealer');

    foreach ($mappedData as $key => $data) {
      $manager->persist($this->importer->fromData($data));
    }

    // Flush DB changes
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
