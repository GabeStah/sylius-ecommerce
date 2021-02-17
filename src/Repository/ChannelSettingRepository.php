<?php

namespace App\Repository;

use App\Entity\Channel\Channel;
use App\Entity\Channel\ChannelSetting;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * @method ChannelSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChannelSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChannelSetting[]    findAll()
 * @method ChannelSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChannelSettingRepository extends EntityRepository
{
  /**
   * @param string       $code
   * @param Channel|null $channel
   *
   * @return ChannelSetting|null
   * @throws NonUniqueResultException
   */
  public function findOneByCode(
    string $code,
    ?Channel $channel
  ): ?ChannelSetting {
    $code = strtolower($code);
    return $this->createQueryBuilder('c')
      ->andWhere('c.code = :code')
      ->andWhere('c.channel = :channel')
      ->andWhere('c.enabled = :enabled')
      ->setParameter('code', $code)
      ->setParameter('channel', $channel->getId())
      ->setParameter('enabled', true)
      ->getQuery()
      ->getOneOrNullResult();
  }
}
