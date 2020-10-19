<?php

namespace App\Service\Converters;

use App\Entity\Taxonomy\Taxon;
use App\Service\Logger;
use Doctrine\DBAL\Exception;
use Sylius\Component\Core\Model\TaxonImageInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use App\Service\StringNormalizer;

class CategoryConverter extends BaseConverter
{
  /**
   * @var TaxonInterface
   */
  private $taxonInterface;
  /**
   * @var TaxonImageInterface
   */
  private $taxonImageInterface;

  public function map()
  {
    $data = $this->execute();
    return array_map(function ($item) {
      return $this->normalizeEntity($item);
    }, $data);
  }

  public function normalizeEntity($item)
  {
    return (object) [
      //      'enabled' => boolval(!!$item['catstatus']),
      'enabled' => boolval(
        is_null($item['catstatus']) ? false : $item['catstatus']
      ),
      'name' => StringNormalizer::toTitle($item['pcategoryname']),
      'slug' => StringNormalizer::toSlug($item['pcategoryurl']),
    ];
  }

  public function create()
  {
    $taxonFactory = $this->container->get('sylius.repository.taxon');
    $taxonFactory->findBy([]);

    $taxon = new Taxon();
    //    $taxon->set

    $taxonFactory = $this->container->get('sylius.factory.taxon');
    $this->taxonInterface = $taxonFactory->createNew();
    $this->taxonInterface->setName('');
    $this->taxonInterface->setSlug('');
    $this->taxonInterface->setDescription('');
    $this->taxonInterface->setEnabled('');

    $taxonImageFactory = $this->container->get('sylius.factory.taxon_image');
    $this->taxonImageInterface = $taxonImageFactory->createNew();

    $taxonImageRepository = $this->container->get(
      'sylius.repository.taxon_image'
    );
    $taxonImageRepository->add($this->taxonInterface);
    /**
     * @var TaxonImageInterface
     */
    $taxonImageInterface = $taxonImageFactory->createNew();
  }

  /**
   * @return array
   * @throws \Doctrine\DBAL\Driver\Exception
   * @throws Exception
   */
  public function execute()
  {
    $converterData = $this->query('SELECT * FROM productcategory');
    Logger::print(json_encode(count($converterData)));
    return $converterData;
  }
}
