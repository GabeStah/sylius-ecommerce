<?php

namespace App\Service\Importer;

use App\Entity\Taxonomy\Taxon;
use App\Service\Logger;
use App\Service\StringNormalizer;
use Doctrine\DBAL\Exception;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import categories from Raritan-v1 db.
 *
 * Class CategoryImporter
 *
 * @package App\Service\Importers
 */
class CategoryImporter extends AbstractImporter implements
  AbstractImporterInterface
{
  protected $modelName = 'category';
  /**
   * @var TaxonRepository
   */
  private $repository;

  /**
   * BaseConverter constructor.
   *
   * @param ContainerInterface $container
   *
   * @param TaxonRepository    $taxonRepository
   *
   * @throws Exception
   */
  public function __construct(
    ContainerInterface $container,
    TaxonRepository $taxonRepository
  ) {
    parent::__construct($container);
    $this->repository = $taxonRepository;
  }

  /**
   * Create entity from data.
   *
   * @param Taxon $entity
   *
   * @return Taxon
   */
  public function create(Taxon $entity)
  {
    $existingEntity = $this->repository->findOneByCode($entity->getCode());
    if ($existingEntity) {
      Logger::print('FOUND, updating');
      $this->repository->add($entity->update($existingEntity));
    } else {
      Logger::print('NOT found, adding');
      $this->repository->add($entity);
    }
    return $entity;
  }

  /**
   * Add extra categories prior to import.
   *
   * Includes miscellaneous category as catch-all for all non-categorized products.
   *
   * @return array|array[]|mixed
   */
  public function extra()
  {
    return [
      [
        'category_id' => 100,
        'category_type' => 'category',
        'code' => StringNormalizer::toSnake('category-' . 'miscellaneous'),
        'description' => null,
        'enabled' => boolval(true),
        'name' => StringNormalizer::toTitle('Miscellaneous'),
        'parent' => [
          'category_id' => 999,
          'category_type' => 'menu',
        ],
        'slug' => StringNormalizer::toSlug('miscellaneous'),
        'timestamp' => time(),
      ],
    ];
  }

  /**
   * Create Entity from data.
   *
   * @param $data
   *
   * @return Taxon
   */
  public function fromData($data): Taxon
  {
    $taxon = $this->repository->findOneByCode($data['code']);
    $isNew = false;
    if (!$taxon) {
      $isNew = true;
      $taxon = new Taxon();
    }

    $taxon->setCurrentLocale($this->getLocale());
    $taxon->setCode($data['code']);
    $taxon->setDescription($data['description']);
    $taxon->setEnabled(is_bool($data['enabled']) ? $data['enabled'] : true);
    $taxon->setName($data['name']);
    $taxon->setSlug($data['slug']);
    $taxon->setCategoryId($data['category_id']);
    $taxon->setCategoryType($data['category_type']);
    if (array_key_exists('parent', $data)) {
      if ($data['parent'] instanceof Taxon) {
        $taxon->setParent($data['parent']);
      } else {
        $parent = $this->repository->findOneByCategory(
          $data['parent']['category_id'],
          $data['parent']['category_type']
        );
        if ($parent) {
          $taxon->setParent($parent);
        } else {
          Logger::print(
            'ERROR: Cannot find parent matching ' .
              $data['parent']['category_id']
          );
        }
      }
    }

    if ($isNew) {
      $this->repository->add($taxon);
    }

    return $taxon;
  }
}
