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
  /**
   * @author https://stackoverflow.com/users/976309/denil
   * @source https://stackoverflow.com/a/7672821/3881247
   */
  const DISTANCE_FORMULA = '3956 * 2 * ASIN(SQRT(POWER(SIN((:latitude - d.latitude) * pi()/180 / 2), 2) + COS(:latitude * pi()/180) * COS(d.latitude * pi()/180) *
                POWER(SIN((:longitude - d.longitude) * pi()/180 / 2), 2) ))';

  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Dealer::class);
  }

  /**
   * Find all Dealers within `distance` (mi) of passed coordinates.
   *
   * @param     $latitude
   * @param     $longitude
   * @param int $distance
   *
   * @return Dealer[]|null
   */
  public function findByDistanceFromPoint(
    $latitude,
    $longitude,
    $distance = 200
  ): ?array {
    if (!$latitude || !$longitude) {
      return null;
    }

    return $this->getEntityManager()
      ->createQueryBuilder()
      ->from('App\Entity\Dealer', 'd')
      ->select('d')
      ->groupBy('d.id HAVING (' . static::DISTANCE_FORMULA . ') <= :distance')
      ->orderBy(static::DISTANCE_FORMULA, 'ASC')
      ->setParameter('latitude', $latitude)
      ->setParameter('longitude', $longitude)
      ->setParameter('distance', $distance)
      ->getQuery()
      ->getResult();
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
