<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\Component\Core\Model\OrderInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order")
 */
class Order extends BaseOrder implements OrderInterface
{
  /** @ORM\Column(length=16777215, nullable=true) */
  private $rates;

  /**
   * @return mixed
   */
  public function getRates()
  {
    return is_string($this->rates) ? unserialize($this->rates) : null;
  }

  /**
   * @param mixed $rates
   */
  public function setRates($rates): void
  {
    $this->rates = serialize($rates);
  }
}
