<?php

namespace App\Command\Export;

use App\Service\Exporter\ProductExporter;
use App\Service\Logger;
use Exception;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductExportCommand extends AbstractExportCommand
{
  protected static $defaultName = 'export:product';
  private $exporter;

  public function __construct(?string $name, ProductExporter $exporter)
  {
    $this->exporter = $exporter;
    parent::__construct($name);
  }

  protected function configure()
  {
    $this->setDescription('Exports a collection of products.')
      ->setHelp('This command exports a collection of products.')
      ->setDefinition(
        new InputDefinition([
          new InputOption(
            'product',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Product code.',
            null
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
  protected function execute(
    InputInterface $input,
    OutputInterface $output
  ): int {
    parent::execute($input, $output);

    $productId = $input->getOption('product');

    if (!$productId) {
      $output->write('Invalid product code, aborting.');
      return 0;
    }

    Logger::print('Exporting product data');
    $this->exporter->export($productId);

    // Should return exit status code
    return 0;
  }
}
