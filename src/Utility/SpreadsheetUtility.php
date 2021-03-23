<?php

namespace App\Utility;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

class SpreadsheetUtility
{
  /**
   * Write documents.
   *
   * @param Spreadsheet $spreadsheet
   * @param string      $path
   * @param string      $writerType
   *
   * @throws Exception
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public static function write(
    Spreadsheet $spreadsheet,
    string $path,
    string $writerType = 'Xlsx'
  ): void {
    $spreadsheet->setActiveSheetIndex(0);

    $writer = IOFactory::createWriter($spreadsheet, $writerType);
    $writer->save($path);
  }
}
