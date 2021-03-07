<?php

namespace App\Repository;

use App\Entity\File\File;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends EntityRepository
{
  /**
   * @param string $checksum
   *
   * @return File|null
   */
  public function findOneByChecksum(string $checksum): ?File
  {
    return $this->findOneBy(['checksum' => $checksum]);
  }
}
