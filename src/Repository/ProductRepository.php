<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;

class ProductRepository extends BaseProductRepository implements
  ProductRepositoryInterface
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
  public function findBySlug(
    string $slug,
    string $locale = 'en_US'
  ): ?ProductInterface {
    return $this->createQueryBuilder('o')
      ->innerJoin(
        'o.translations',
        'translation',
        'WITH',
        'translation.locale = :locale'
      )
      ->andWhere('translation.slug = :slug')
      ->setParameter('slug', $slug)
      ->setParameter('locale', $locale)
      ->getQuery()
      ->getOneOrNullResult();
  }

  /**
   * Find Products by term.
   *
   * Finds matches against name, description, or taxon.
   *
   * @source https://stackoverflow.com/a/52851555/3881247
   *
   * @param ChannelInterface $channel
   * @param string           $locale
   * @param                  $searchTerm
   *
   * @return array|Pagerfanta
   */
  public function findByTerm(
    ChannelInterface $channel,
    string $locale,
    $searchTerm
  ) {
    $qb = $this->createQueryBuilder('p')
      ->addSelect('translation')
      ->innerJoin(
        'p.translations',
        'translation',
        'WITH',
        'translation.locale = :locale'
      )
      ->orWhere('translation.name LIKE :searchTerm')
      ->orWhere('translation.description LIKE :searchTerm')
      ->innerJoin('p.productTaxons', 'productTaxon')
      ->innerJoin('productTaxon.taxon', 'taxon')
      ->innerJoin(
        'taxon.translations',
        'taxonTranslation',
        'WITH',
        'taxonTranslation.locale = :locale'
      )
      ->orWhere('taxonTranslation.name LIKE :searchTerm')
      ->andWhere(':channel MEMBER OF p.channels')
      ->andWhere('p.enabled = true')
      ->setParameter('searchTerm', '%' . $searchTerm . '%')
      ->setParameter('locale', $locale)
      ->setParameter('channel', $channel);

    return $qb->getQuery()->getResult();
  }
}
