<?php

namespace App\Shipping\Calculator;

use App\Entity\Addressing\Address;
use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use Solarix\Shipping\Factory\AddressFactory;
use Solarix\Shipping\Model\AddressInterface;
use Solarix\Shipping\Model\Rate\RateInterface;
use Solarix\Shipping\Provider\FedExProvider;
use Sylius\Component\Shipping\Calculator\CalculatorInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;

final class FedExRateCalculator implements CalculatorInterface
{
  /**
   * @var FedExProvider
   */
  private $shippingProvider;

  /**
   * @param FedExProvider $shippingProvider
   */
  public function __construct(FedExProvider $shippingProvider)
  {
    $this->shippingProvider = $shippingProvider;
  }

  public function calculate(
    ShipmentInterface $subject,
    array $configuration
  ): int {
    $order = $subject->getOrder();
    if ($this->isOrderRatesDataStale($order)) {
      // If stale update Order Rates data from FedEx API provider response
      $this->updateOrderRatesData($order, $this->getRatesResponse($subject));
    }
    // Get Rate instance from Order matching code.
    /** @var RateInterface $rate */
    $rate = $this->findOrderRateByCode($order, $configuration['code']);
    if (!$rate) {
      return 0;
    }
    $baseCharge = $rate->getBaseCharge() * 100;
    return (int) intval(
      $configuration['minimum'] > $baseCharge
        ? $configuration['minimum']
        : $baseCharge
    );
  }

  /**
   * @param ShipmentInterface $subject
   *
   * @return RateInterface[]|null
   */
  private function getRatesResponse(ShipmentInterface $subject)
  {
    // Create a Shipment
    $shipment = $this->shippingProvider->getShipmentFactory()->create();

    // Create a ShippableUnit
    $shippableUnit = $this->shippingProvider
      ->getShippableUnitFactory()
      ->create();

    /** @var OrderItem $orderItem */
    foreach ($subject->getOrder()->getItems() as $orderItem) {
      $variant = $orderItem->getVariant();

      // Create Shippable with properties and add to ShippableUnit
      $shippableUnit->addShippable(
        $this->shippingProvider
          ->getShippableFactory()
          ->create()
          ->setDepth($variant->getShippingDepth())
          ->setHeight($variant->getShippingHeight())
          ->setId($variant->getId())
          ->setQuantity($orderItem->getQuantity())
          ->setWeight($variant->getShippingWeight())
          ->setWidth($variant->getShippingWidth())
      );
    }

    // Add ShippableUnit to Shipment
    $shipment->addShippableUnit($shippableUnit);

    // Create default addresses
    $originAddress = $this->shippingProvider
      ->getAddressFactory()
      ->default(AddressFactory::ORIGIN_TYPE);
    $destinationAddress = $this->shippingProvider
      ->getAddressFactory()
      ->default(AddressFactory::DESTINATION_TYPE);

    // Get addresses from order
    /** @var Address $billingAddress */
    $orderBillingAddress = $subject->getOrder()->getBillingAddress();
    /** @var Address $shippingAddress */
    $orderShippingAddress = $subject->getOrder()->getShippingAddress();

    if ($orderShippingAddress) {
      // Use Order-specific shipping address to override default destination address as a SolarixShipping::Address
      $destinationAddress = $this->toSolarixAddress($orderShippingAddress);
    } elseif ($orderBillingAddress) {
      // If no shipping address use specified order address
      $destinationAddress = $this->toSolarixAddress($orderBillingAddress);
    }

    $shipment->setOrigin($originAddress);
    $shipment->setDestination($destinationAddress);

    // Create and make provider RateRequest
    $rateRequest = $this->shippingProvider
      ->getRateRequestFactory()
      ->create($shipment);
    $rateResponse = $rateRequest->makeRequest();

    if (!$rateResponse->hasError()) {
      // Return rates if no error
      return $rateResponse->getRates();
    }
    return null;
  }

  /**
   * Determine if Order's Rate data is outdated.
   *
   * @param Order $order
   * @param int   $elapsedSeconds Number of seconds since last update after which data is considered stale
   *
   * @return bool
   */
  private function isOrderRatesDataStale(Order $order, $elapsedSeconds = 30)
  {
    $data = $order->getRates();
    if (!$data) {
      return true;
    }
    if (!array_key_exists('updated_at', $data)) {
      return true;
    }

    if (time() - $data['updated_at'] >= $elapsedSeconds) {
      return true;
    }
    return false;
  }

  /**
   * Get specific Rate object from Order based on code.
   *
   * @param Order  $order
   *
   * @param string $code
   *
   * @return RateInterface|null
   */
  private function findOrderRateByCode(
    Order $order,
    string $code
  ): ?RateInterface {
    if (!$order->getRates()) {
      return null;
    }
    if (!array_key_exists('rates', $order->getRates())) {
      return null;
    }
    /** @var RateInterface $rate */
    foreach ($order->getRates()['rates'] as $rate) {
      if ($code == $rate->getId()) {
        return $rate;
      }
    }
    return null;
  }

  /**
   * Update Rates data for the passed Order instance.
   *
   * @param Order                $order
   * @param RateInterface[]|null $rates
   */
  private function updateOrderRatesData(Order $order, ?array $rates): void
  {
    if (!$rates) {
      return;
    }

    $order->setRates([
      'updated_at' => time(),
      'rates' => $rates,
    ]);
  }

  public function getType(): string
  {
    return 'fedex';
  }

  /**
   * Map Sylius address to SolarixShipping Address.
   *
   * @param Address $source
   *
   * @return AddressInterface
   */
  private function toSolarixAddress(Address $source): AddressInterface
  {
    return $this->shippingProvider
      ->getAddressFactory()
      ->create()
      ->setCity($source->getCity())
      ->setCompanyName($source->getCompany())
      ->setCountryCode($source->getCountryCode())
      ->setFirstName($source->getFirstName())
      ->setLastName($source->getLastName())
      ->setPhone($source->getPhoneNumber())
      ->setPostalCode($source->getPostcode())
      ->setStateOrProvinceCode($source->getProvinceCode())
      ->setStreetLines([$source->getStreet()]);
  }
}
