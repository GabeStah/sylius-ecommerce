<?php

namespace App\EmailManager;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class OrderPaidEmailManager
{
  /** @var SenderInterface */
  private $emailSender;

  /** @var AvailabilityCheckerInterface */
  private $availabilityChecker;

  /** @var RepositoryInterface $adminUserRepository */
  private $adminUserRepository;

  public function __construct(
    SenderInterface $emailSender,
    AvailabilityCheckerInterface $availabilityChecker,
    RepositoryInterface $adminUserRepository
  ) {
    $this->emailSender = $emailSender;
    $this->availabilityChecker = $availabilityChecker;
    $this->adminUserRepository = $adminUserRepository;
  }

  public function sendOrderPaidEmail(OrderInterface $order): void
  {
    $this->emailSender->send(
      'order_paid',
      [$order->getCustomer()->getEmail()],
      [
        'order' => $order,
        'channel' => $order->getChannel(),
        'localeCode' => $order->getLocaleCode(),
      ]
    );
  }
}
