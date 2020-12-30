<?php

namespace App\Repository;

use App\Entity\Dealer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dealer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dealer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dealer[]    findAll()
 * @method Dealer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DealerRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Dealer::class);
  }

  /**
   * @param     $latitude
   * @param     $longitude
   * @param int $distance
   *
   * @return Dealer[]|null
   */
  public function findByDistanceFromPoint(
    $latitude,
    $longitude,
    $distance = 500
  ): ?array {
    if (!$latitude || !$longitude) {
      return null;
    }

    $entityManager = $this->getEntityManager();

    // TODO: Resolve converting to array and not Entity object
    // error: ncaught PHP Exception Twig\Error\RuntimeError: "Key "typeName" for array with keys "id, name, slug, address1, address2, city, postal_code, state, country, phone, fax, email, url, enabled, type, wholesale, comme
    //nt, latitude, longitude, distance" does not exist." at D:\work\solarix\projects\raritan\sylius\templates\bundles\SyliusShopBundle\Dealer\_dealers-list.html.twig lin

    $query = $entityManager
      ->createQuery(
        'SELECT d.id, d.name, d.slug, d.address1, d.address2, d.city, d.postal_code, d.state, d.country, d.phone, d.fax, d.email, d.url, d.enabled, d.type, d.wholesale, d.comment, d.latitude, d.longitude,
            3956 * 2 * ASIN(SQRT( POWER(SIN((:latitude - d.latitude) * pi()/180 / 2), 2) + COS(:latitude * pi()/180) * COS(d.latitude * pi()/180) *
            POWER(SIN((:longitude - d.longitude) * pi()/180 / 2), 2) )) as
            distance FROM App\Entity\Dealer d
            GROUP BY d.id HAVING distance <= :distance ORDER by distance ASC'
      )
      ->setParameter('latitude', $latitude)
      ->setParameter('longitude', $longitude)
      ->setParameter('distance', $distance);

    //    $query = $this->createQueryBuilder('d')
    //      ->select(
    //        '3956 * 2 * ASIN(SQRT(POWER(SIN((:latitude - d.latitude) * pi()/180 / 2), 2) + COS(:latitude * pi()/180) * COS(d.latitude * pi()/180) * POWER(SIN((:longitude - d.longitude) * pi()/180 / 2), 2))) as distance'
    //      )
    //      ->addSelect(
    //        'd.id, d.name, d.slug, d.address1, d.address2, d.city, d.postal_code, d.state, d.country, d.phone, d.fax, d.email, d.url, d.enabled, d.type, d.wholesale, d.comment, d.latitude, d.longitude'
    //      )
    //      ->addGroupBy('d.id HAVING distance <= :distance ORDER by distance ASC')
    //      ->setParameter('latitude', $latitude)
    //      ->setParameter('longitude', $longitude)
    //      ->setParameter('distance', $distance)
    //      ->getQuery();

    return $query->getResult();
  }

  // /**
  //  * @return Dealer[] Returns an array of Dealer objects
  //  */
  /*
  public function findByExampleField($value)
  {
      return $this->createQueryBuilder('d')
          ->andWhere('d.exampleField = :val')
          ->setParameter('val', $value)
          ->orderBy('d.id', 'ASC')
          ->setMaxResults(10)
          ->getQuery()
          ->getResult()
      ;
  }
  */

  /*
  public function findOneBySomeField($value): ?Dealer
  {
      return $this->createQueryBuilder('d')
          ->andWhere('d.exampleField = :val')
          ->setParameter('val', $value)
          ->getQuery()
          ->getOneOrNullResult()
      ;
  }
  */
}
