<?php

namespace App\Repository;

use App\Entity\Product\ProductFile;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * @method ProductFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductFile[]    findAll()
 * @method ProductFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductFileRepository extends EntityRepository
{
}
