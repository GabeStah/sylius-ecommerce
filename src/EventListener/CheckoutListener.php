<?php

namespace App\EventListener;

use App\Entity\Order\Order;
use Doctrine\Common\Persistence\ObjectManager;
use Solarix\Shipping\Provider\FedEx\Model\Rate\Rate;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckoutListener
{
  /** @var RouterInterface */
  private $router;

  /** @var ObjectManager */
  private $manager;

  /** @var TranslatorInterface */
  private $translator;

  public function __construct(
    RouterInterface $router,
    ObjectManager $manager,
    TranslatorInterface $translator
  ) {
    $this->router = $router;
    $this->manager = $manager;
    $this->translator = $translator;
  }

  /**
   * @param string                  $message
   * @param ResourceControllerEvent $event
   * @param Order                   $order
   */
  private function showError(
    string $message,
    ResourceControllerEvent $event,
    Order $order
  ) {
    $event->stop($message, ResourceControllerEvent::TYPE_ERROR);
    $event->setResponse(
      new RedirectResponse(
        $this->router->generate('sylius_shop_checkout_select_shipping')
      )
    );

    $this->manager->persist($order);
    $this->manager->flush();
  }

  /**
   * Determine if shipping id is valid.
   *
   * Checks for zero-value base charge.
   *
   * @param $id
   * @param $rates
   *
   * @return bool
   */
  private function isShippingIdValid($id, $rates): bool
  {
    return array_reduce(
      $rates,
      function ($carry, $item) use ($id) {
        if ($item->getId() === $id) {
          $carry = $item->getBaseCharge() !== 0;
        }
        return $carry;
      },
      false
    );
  }

  public function onPreAddress(ResourceControllerEvent $event)
  {
    $order = $event->getSubject();
  }

  public function onPreSelectShipping(ResourceControllerEvent $event)
  {
    /** @var Order $order */
    $order = $event->getSubject();

    $rates = $order->getRates();
    if (!$rates) {
      // No rates data
      $this->showError(
        $this->translator->trans(
          'checkout.select_shipping.zero_shipping_total_error'
        ),
        $event,
        $order
      );
    }

    /** @var Rate[] $rateCollection */
    $rateCollection = $rates['rates'];
    if (!is_array($rateCollection) || count($rateCollection) === 0) {
      // No rate items
      $this->showError(
        $this->translator->trans(
          'checkout.select_shipping.zero_shipping_total_error'
        ),
        $event,
        $order
      );
    }

    $selectedShippingId =
      $_REQUEST['sylius_checkout_select_shipping']['shipments'][0]['method'];

    if (!$this->isShippingIdValid($selectedShippingId, $rateCollection)) {
      // Invalid shipping selection
      $this->showError(
        $this->translator->trans(
          'checkout.select_shipping.zero_shipping_total_error'
        ),
        $event,
        $order
      );
    }
  }

  public function onPrePayment(ResourceControllerEvent $event)
  {
    $order = $event->getSubject();
  }

  public function onPreComplete(ResourceControllerEvent $event)
  {
    $order = $event->getSubject();
  }

  public function onPostAddress(ResourceControllerEvent $event)
  {
    $order = $event->getSubject();
  }

  public function onPostSelectShipping(ResourceControllerEvent $event)
  {
    $order = $event->getSubject();
  }

  public function onPostPayment(ResourceControllerEvent $event)
  {
    $order = $event->getSubject();
  }

  public function onPostComplete(ResourceControllerEvent $event)
  {
    $order = $event->getSubject();
  }
}
