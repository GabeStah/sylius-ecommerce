<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface as BaseProductRepositoryInterface;

interface ProductRepositoryInterface extends BaseProductRepositoryInterface
{
  public function findBySlug(string $slug, string $locale = 'en_US'): ?ProductInterface;
}
