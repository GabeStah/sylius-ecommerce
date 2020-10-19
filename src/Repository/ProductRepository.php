<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;
use Sylius\Component\Core\Model\ProductInterface;

class ProductRepository extends BaseProductRepository implements ProductRepositoryInterface
{
  /**
   * Find Product by slug.
   *
   * @param string $slug
   * @param string $locale
   *
   * @return ProductInterface|null
   * @throws NonUniqueResultException
   */
  public function findBySlug(string $slug, string $locale = 'en_US'): ?ProductInterface {
    return $this->createQueryBuilder('o')
                ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
                ->andWhere('translation.slug = :slug')
                ->setParameter('slug', $slug)
                ->setParameter('locale', $locale)
                ->getQuery()
                ->getOneOrNullResult();
  }
}
