<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use App\Model\Taxonomy\Taxon as TaxonModel;
use App\Service\Logger;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Sylius\Component\Core\Model\Taxon as BaseTaxon;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_taxon")
 */
class Taxon extends BaseTaxon
{
  /** @ORM\Column(type="smallint", nullable=true) */
  private $category_id;
  /** @ORM\Column(type="string", nullable=true) */
  private $category_type;

  /**
   * @return int|null
   */
  public function getCategoryId(): ?int
  {
    return $this->category_id;
  }

  /**
   * @param int|null $category_id
   */
  public function setCategoryId(?int $category_id): void
  {
    $this->category_id = $category_id;
  }

  /**
   * @return string|null
   */
  public function getCategoryType(): ?string
  {
    return $this->category_type;
  }

  /**
   * @param string|null $category_type
   */
  public function setCategoryType(?string $category_type): void
  {
    $this->category_type = $category_type;
  }

  protected function createTranslation(): TaxonTranslationInterface
  {
    return new TaxonTranslation();
  }

  /**
   * Create Entity from model.
   *
   * @param TaxonModel $model
   *
   * @return $this
   */
  public function fromModel(TaxonModel $model): Taxon
  {
    $this->setCurrentLocale('en_US');
    $this->setCode($model->getCode());
    if (count($model->getDescriptions()) > 0) {
      $this->setDescription($model->getDescriptions()[0]);
    }
    $this->setEnabled($model->isEnabled());
    $this->setSlug($model->getSlug());
    $this->setName($model->getName());
    if ($model->getParent()) {
      $this->setParent($this->fromModel($model->getParent()));
    }
    return $this;
  }

  /**
   * Create Entity from model.
   *
   * @param $data
   *
   * @return $this
   */
  public function fromData($data): Taxon
  {
    $this->setCurrentLocale('en_US');
    $this->setCode($data['code']);
    $this->setDescription(
      $data['descriptions'] ? $data['descriptions'][0] : null
    );
    $this->setEnabled(is_bool($data['enabled']) ? $data['enabled'] : true);
    $this->setName($data['name']);
    $this->setSlug($data['slug']);
    if (array_key_exists('parent', $data)) {
      //      if ($data['parent'] instanceof Taxon) {
      $this->setParent($data['parent']);
      //      } else {
      //        Logger::print('PARENT');
      //        Logger::print($data['parent']);
      //        $existingEntity = $this->repository->findOneByCode($data['parent']);
      //      }
    }

    return $this;
  }

  /**
   * Update existing Entity from self.
   *
   * @param Taxon $target
   *
   * @return TaxonInterface
   */
  public function update(Taxon $target): TaxonInterface
  {
    $target->setCurrentLocale('en_US');
    $target->setCode($this->getCode());
    $target->setDescription($this->getDescription());
    $target->setEnabled($this->isEnabled());
    $target->setSlug($this->getSlug());
    $target->setName($this->getName());
    if ($this->getParent()) {
      $target->setParent($this->getParent());
    }

    return $target;
  }
}
