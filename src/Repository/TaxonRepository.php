<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository as BaseRepository;
use Sylius\Component\Core\Model\TaxonInterface;

class TaxonRepository extends BaseRepository implements TaxonRepositoryInterface
{
  /**
   * Find one by code.
   *
   * @param string $code
   * @param string $locale
   *
   * @return TaxonInterface|null
   * @throws NonUniqueResultException
   */
  public function findOneByCode(
    string $code,
    string $locale = 'en_US'
  ): ?TaxonInterface {
    return $this->createQueryBuilder('o')
      ->addSelect('translation')
      ->innerJoin('o.translations', 'translation')
      ->andWhere('o.code = :code')
      ->setParameter('code', $code)
      ->getQuery()
      ->getOneOrNullResult();
  }

  /**
   * Find one by category.
   *
   * @param int    $category_id
   * @param string $category_type
   * @param string $locale
   *
   * @return TaxonInterface|null
   * @throws NonUniqueResultException
   */
  public function findOneByCategory(
    int $category_id,
    string $category_type = 'category',
    string $locale = 'en_US'
  ): ?TaxonInterface {
    return $this->createQueryBuilder('o')
      ->addSelect('translation')
      ->innerJoin('o.translations', 'translation')
      ->andWhere('o.category_id = :category_id')
      ->andWhere('o.category_type = :category_type')
      ->setParameter('category_id', $category_id)
      ->setParameter('category_type', $category_type)
      ->getQuery()
      ->getOneOrNullResult();
  }
}
