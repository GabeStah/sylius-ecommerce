<?php

namespace App\Command\Import;

use App\Entity\Addressing\Province;
use App\Entity\Addressing\Zone;
use App\Entity\Addressing\ZoneMember;
use App\Entity\Taxation\TaxCategory;
use App\Entity\Taxation\TaxRate;
use App\Service\Importer\ZoneImporter;
use App\Service\Logger;
use Exception;
use Swaggest\JsonSchema\Schema;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ZoneImportCommand extends AbstractImportCommand
{
  protected static $defaultName = 'import:zone';
  private $importer;
  /**
   * @var EntityRepository|null
   */
  private $provinceRepository;
  /**
   * @var EntityRepository|null
   */
  private $zoneRepository;
  /**
   * @var EntityRepository|null
   */
  private $zoneMemberRepository;
  /**
   * @var EntityRepository|null
   */
  private $countryRepository;
  /**
   * @var EntityRepository|null
   */
  private $taxCategoryRepository;
  /**
   * @var EntityRepository|null
   */
  private $taxRateRepository;

  public function __construct(?string $name) {
    parent::__construct($name);
  }

  protected function configure() {
    $this->setDescription('Imports zones.');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void
   * @throws Exception|\Doctrine\DBAL\Driver\Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    parent::execute($input, $output);

    $this->countryRepository = $this->get('sylius.repository.country');
    $this->provinceRepository = $this->get('sylius.repository.province');
    $this->taxCategoryRepository = $this->get('sylius.repository.tax_category');
    $this->taxRateRepository = $this->get('sylius.repository.tax_rate');
    $this->zoneRepository = $this->get('sylius.repository.zone');
    $this->zoneMemberRepository = $this->get('sylius.repository.zone_member');

    $provinceData = json_decode(
      file_get_contents('seed/province-tax-nj-only.json'),
      true
    );
    Logger::print(count($provinceData));

    $this->importProvinces($provinceData);
    $this->importZonesFromProvinces($provinceData);
    $this->importTaxRatesFromProvinces($provinceData);

    // Should return exit status code
    return 0;
  }

  private function importProvinces($data) {
    foreach ($data as $datum) {
      $this->importProvince($datum);
    }
  }

  private function importProvince($data) {
    Logger::print('Importing Province: ' . $data['code']);

    $entity = $this->provinceRepository->findOneBy(['code' => $data['code']]);
    if (!$entity) {
      $entity = new Province();
    }

    $entity->setName($data['name']);
    $entity->setCountry(
      $this->countryRepository->findOneBy(['code' => $data['country']])
    );
    $entity->setAbbreviation($data['abbreviation']);
    $entity->setCode($data['code']);
    $this->provinceRepository->add($entity);
  }

  private function importZonesFromProvinces($data) {
    foreach ($data as $datum) {
      $this->importZoneFromProvince(
        $this->provinceRepository->findOneBy(['code' => $datum['code']])
      );
    }
  }

  private function importZoneFromProvince(Province $province) {
    Logger::print('Importing Zone from Province: ' . $province->getCode());

    $code = $this->getZoneCodeFromProvince($province);
    $name = $this->getZoneNameFromProvince($province);

    $entity = $this->zoneRepository->findOneBy(['code' => $code]);
    if (!$entity) {
      $entity = new Zone();
    }

    $entity->setCode($code);
    $entity->setName($name);
    $entity->setScope('tax');
    $entity->setType('province');

    $zoneMember = $this->zoneMemberRepository->findOneBy([
      'code' => $province->getCode(),
    ]);

    if (!$zoneMember) {
      $zoneMember = new ZoneMember();
    }

    $zoneMember->setCode($province->getCode());

    $entity->addMember($zoneMember);
    $this->zoneRepository->add($entity);
  }

  private function getZoneCodeFromProvince(Province $province): string {
    return 'ZONE-' . $province->getCode();
  }

  private function getZoneNameFromProvince(Province $province): string {
    return $province->getCountry()
                    ->getCode() . ' - ' . $province->getName();
  }

  private function importTaxRatesFromProvinces($data) {
    $taxCategory = $this->taxCategoryRepository->findOneBy([
      'code' => 'taxable_good',
    ]);

    foreach ($data as $datum) {
      $province = $this->provinceRepository->findOneBy([
        'code' => $datum['code'],
      ]);
      $this->importTaxRateFromZone(
        $this->zoneRepository->findOneBy([
          'code' => $this->getZoneCodeFromProvince($province),
        ]),
        $taxCategory,
        $datum['rate'],
        $datum['code']
      );
    }
  }

  private function importTaxRateFromZone(
    Zone $zone,
    TaxCategory $taxCategory,
    float $rate,
    string $code
  ) {
    Logger::print(
      'Importing TaxRate from Zone: ' . $zone->getCode() . ' (' . $rate . ')'
    );

    $entity = $this->taxRateRepository->findOneBy(['code' => $code]);
    if (!$entity) {
      $entity = new TaxRate();
    }

    $entity->setAmount($rate ?? 0);
    $entity->setCalculator('default');
    $entity->setCategory($taxCategory);
    $entity->setCode($code);
    $entity->setIncludedInPrice(false);
    $entity->setName($zone->getName());
    $entity->setZone($zone);

    $this->taxRateRepository->add($entity);
  }

  public function validateSchema($data) {
    $schema = Schema::import('schema/zone.json');
    return $schema->in($data);
  }
}
