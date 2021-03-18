<?php

namespace App\Command\Conversion;

use App\Service\Logger;
use App\Service\XLSXWriter;
use Exception;
use Sylius\Bundle\CoreBundle\Command\AbstractInstallCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JsonToSpreadsheetCommand extends AbstractInstallCommand
{
  protected static $defaultName = 'convert:json2spreadsheet';
  private $logging = false;

  public function __construct()
  {
    parent::__construct();
    $this->addArgument('input', InputArgument::REQUIRED, 'Path to input file.');
    $this->addArgument(
      'output',
      InputArgument::OPTIONAL,
      'Path to output file.'
    );

    $this->addOption(
      'log',
      'l',
      InputOption::VALUE_OPTIONAL,
      'Logs output.',
      false
    );

    $this->addOption(
      'property',
      'p',
      InputOption::VALUE_OPTIONAL,
      'Property path to convert.',
      null
    );
  }

  /**
   * @return bool
   */
  public function isLogging(): bool
  {
    return $this->logging;
  }

  /**
   * @param bool $logging
   */
  public function setLogging(bool $logging): void
  {
    $this->logging = $logging;
  }

  protected function configure()
  {
    $this->setDescription('Converts data.');
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
    $this->setLogging($input->getOption('log') !== false);
    $output->writeln(['==================', get_class(), '==================']);

    $inputPath = $input->getArgument('input');
    $outputPath =
      $input->getArgument('output') ??
      'exports/converted/' . pathinfo($inputPath, PATHINFO_FILENAME) . '.xlsx';

    $property = $input->getOption('property');

    $content = json_decode(file_get_contents($inputPath), true);

    if ($property) {
      // Get desired content from exploded property path
      $propertyPaths = explode('.', $property);
      $content = array_reduce(
        $propertyPaths,
        function ($carry, $item) {
          $carry = $carry[$item];
          return $carry;
        },
        $content
      );
    }

    // Headers
    $headers = [];
    $item = $content[0];
    foreach ($item as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $subkey => $subvalue) {
          $headers[$key . '.' . $subkey] = gettype($subvalue);
        }
      } else {
        $headers[$key] = gettype($value);
      }
    }

    // Rows
    $rows = [];
    foreach ($content as $item) {
      $row = [];
      foreach ($item as $key => $value) {
        if (is_array($value)) {
          foreach ($value as $subvalue) {
            $row[] = $subvalue;
          }
        } else {
          $row[] = $value;
        }
      }
      $rows[] = $row;
    }

    $writer = new XLSXWriter();

    $writer->writeSheetHeader('Sheet1', $headers);
    foreach ($rows as $row) {
      $writer->writeSheetRow('Sheet1', $row);
    }

    $writer->writeToFile($outputPath);

    // Should return exit status code
    return 0;
  }

  public function log($value)
  {
    if ($this->isLogging()) {
      Logger::print($value);
    }
  }
}
