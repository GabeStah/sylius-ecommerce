<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface as BaseRepositoryInterface;

interface TaxonRepositoryInterface extends BaseRepositoryInterface
{
  public function findOneByCode(
    string $code,
    string $locale = 'en_US'
  ): ?TaxonInterface;

  public function findOneByCategory(
    int $category_id,
    string $category_type = 'category',
    string $locale = 'en_US'
  ): ?TaxonInterface;
}
