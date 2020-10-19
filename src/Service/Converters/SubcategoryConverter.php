<?php

namespace App\Service\Converters;

use App\Service\Logger;

class SubcategoryConverter extends BaseConverter
{
  public function execute()
  {
    $converterData = $this->query('SELECT * FROM productsubcategory');
    Logger::print(json_encode(count($converterData)));
  }
}
