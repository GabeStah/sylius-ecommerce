<?php

namespace App\Command\Convert;

use App\Service\Logger;
use App\Utility\ArrayUtility;
use App\Utility\JsonUtility;
use Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Sylius\Bundle\CoreBundle\Command\AbstractInstallCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class XlsxToJsonCommand extends AbstractInstallCommand
{
  private static $FILE_SUFFIX = 'json';

  protected static $defaultName = 'convert:xlsx:json';
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
      'type',
      't',
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
      'exports/converted/' .
        pathinfo($inputPath, PATHINFO_FILENAME) .
        '.' .
        static::$FILE_SUFFIX;

    $type = $input->getOption('type');

    if ($type) {
      // do something
    }

    $reader = new Xlsx();
    $spreadsheet = $reader->load($inputPath);
    $worksheet = $spreadsheet->getActiveSheet();
    // Convert to array with non-empty cells
    $maxCell = $worksheet->getHighestRowAndColumn();
    $data = $worksheet->rangeToArray(
      'A1:' . $maxCell['column'] . $maxCell['row']
    );

    // Get headers and remove header row from data set
    $headers = $data[0];
    unset($data[0]);

    $outputData = [];
    foreach ($data as $row) {
      if (!ArrayUtility::hasData($row)) {
        break;
      }
      $outputRow = [];
      foreach ($row as $key => $value) {
        if (str_contains($headers[$key], '.')) {
          // Generate multi-dimensional array for dot-notated header values
          ArrayUtility::set($outputRow, $headers[$key], $value);
        } else {
          $outputRow[$headers[$key]] = $value;
        }
      }
      $outputData[] = $outputRow;
    }

    JsonUtility::write($outputPath, $outputData);

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
