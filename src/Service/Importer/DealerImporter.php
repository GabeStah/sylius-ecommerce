<?php

namespace App\Service\Importer;

use App\Entity\Dealer;
use App\Repository\DealerRepository;
use App\Service\Logger;
use App\Service\StringNormalizer;
use Doctrine\DBAL\Exception;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import dealers from Raritan-v1 db.
 *
 * Class DealerImporter
 *
 * @package App\Service\Importers
 */
class DealerImporter extends AbstractImporter implements
  AbstractImporterInterface
{
  protected $modelName = 'dealer';
  /**
   * @var DealerRepository
   */
  private $repository;

  /**
   * BaseConverter constructor.
   *
   * @param ContainerInterface $container
   *
   * @param DealerRepository   $dealerRepository
   *
   * @throws Exception
   */
  public function __construct(
    ContainerInterface $container,
    DealerRepository $dealerRepository
  ) {
    parent::__construct($container);
    $this->repository = $dealerRepository;
  }

  /**
   * Create entity from data.
   *
   * @param Dealer $entity
   *
   * @return Dealer
   */
  public function create(Dealer $entity)
  {
    $existingEntity = $this->repository->findOneBy([
      'slug' => $entity->getSlug(),
    ]);
    if ($existingEntity) {
      Logger::print('FOUND, updating');
      $entity->update($existingEntity);
    } else {
      Logger::print('NOT found, adding');
    }
    return $entity;
  }

  /**
   * Add extras prior to import.
   *
   * @return array|array[]|mixed
   */
  public function extra()
  {
    return [];
  }

  /**
   * Create Dealer from data.
   *
   * @param $data
   *
   * @return Dealer
   */
  public function fromData($data): Dealer
  {
    $entity = $this->repository->findOneBy(['slug' => $data['slug']]);
    if (!$entity) {
      $entity = new Dealer();
    }

    $entity->setAddress1($data['address1']);
    $entity->setAddress2($data['address2']);
    $entity->setCity($data['city']);
    $entity->setComment($data['comment']);
    $entity->setCountry(strtoupper($data['country']));
    $entity->setEmail($data['email']);
    $entity->setEnabled(is_bool($data['enabled']) ? $data['enabled'] : true);
    $entity->setFax($data['fax']);
    $entity->setLatitude($data['latitude']);
    $entity->setLongitude($data['longitude']);
    $entity->setName($data['name']);
    $entity->setPhone($data['phone']);
    $entity->setPostalCode($data['postal_code']);
    $entity->setSlug($data['slug']);
    $entity->setState($data['state']);
    $entity->setType($data['type']);
    $entity->setUrl($data['url']);
    $entity->setWholesale(
      is_bool($data['wholesale']) ? $data['wholesale'] : true
    );

    return $entity;
  }
}
