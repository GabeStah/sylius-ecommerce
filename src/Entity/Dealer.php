<?php

namespace App\Entity;

use App\Repository\DealerRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity(repositoryClass=DealerRepository::class)
 */
class Dealer implements ResourceInterface
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $name;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $slug;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  private $address1;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  private $address2;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  private $city;

  /**
   * @ORM\Column(type="string", length=20, nullable=true)
   */
  private $postal_code;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  private $state;

  /**
   * @ORM\Column(type="string", length=2, nullable=true)
   */
  private $country;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  private $phone;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  private $fax;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $email;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  private $url;

  /**
   * @ORM\Column(type="boolean")
   */
  private $enabled;

  /**
   * @ORM\Column(type="smallint", nullable=true)
   */
  private $type;

  /**
   * @ORM\Column(type="boolean")
   */
  private $wholesale;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private $comment;

  /**
   * @ORM\Column(type="string", length=20, nullable=true)
   */
  private $latitude;

  /**
   * @ORM\Column(type="string", length=20, nullable=true)
   */
  private $longitude;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getSlug(): ?string
  {
    return $this->slug;
  }

  public function setSlug(string $slug): self
  {
    $this->slug = $slug;

    return $this;
  }

  public function getAddress1(): ?string
  {
    return $this->address1;
  }

  public function setAddress1(?string $address1): self
  {
    $this->address1 = $address1;

    return $this;
  }

  public function getAddress2(): ?string
  {
    return $this->address2;
  }

  public function setAddress2(?string $address2): self
  {
    $this->address2 = $address2;

    return $this;
  }

  public function getAddress3(): ?string
  {
    $value = null;
    if ($this->getCity()) {
      $value .= $this->getCity();
    }
    if ($this->getState()) {
      $value .= ', ' . $this->getState();
    }
    if ($this->getPostalCode()) {
      $value .= ' ' . $this->getPostalCode();
    }
    if ($this->getCountry()) {
      $value .= ', ' . $this->getCountry();
    }
    return $value;
  }

  public function getCity(): ?string
  {
    return $this->city;
  }

  public function setCity(?string $city): self
  {
    $this->city = $city;

    return $this;
  }

  public function getPostalCode(): ?string
  {
    return $this->postal_code;
  }

  public function setPostalCode(?string $postal_code): self
  {
    $this->postal_code = $postal_code;

    return $this;
  }

  public function getState(): ?string
  {
    return $this->state;
  }

  public function setState(?string $state): self
  {
    $this->state = $state;

    return $this;
  }

  public function getCountry(): ?string
  {
    return $this->country;
  }

  public function setCountry(?string $country): self
  {
    $this->country = $country;

    return $this;
  }

  public function getPhone(): ?string
  {
    return $this->phone;
  }

  public function setPhone(?string $phone): self
  {
    $this->phone = $phone;

    return $this;
  }

  public function getFax(): ?string
  {
    return $this->fax;
  }

  public function setFax(?string $fax): self
  {
    $this->fax = $fax;

    return $this;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(?string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function getUrl(): ?string
  {
    return $this->url;
  }

  public function setUrl(?string $url): self
  {
    $this->url = $url;

    return $this;
  }

  public function getEnabled(): ?bool
  {
    return $this->enabled;
  }

  public function setEnabled(bool $enabled): self
  {
    $this->enabled = $enabled;

    return $this;
  }

  public function getType(): ?int
  {
    return $this->type;
  }

  public function getTypeName(): ?string
  {
    switch ($this->type) {
      case 1:
        return 'Servicing Dealer';
      case 3:
        return 'International Distributor';
      case 0:
      case 2:
      default:
        return 'Domestic Distributor';
    }
  }

  public function setType(?int $type): self
  {
    $this->type = $type;

    return $this;
  }

  public function getWholesale(): ?bool
  {
    return $this->wholesale;
  }

  public function setWholesale(bool $wholesale): self
  {
    $this->wholesale = $wholesale;

    return $this;
  }

  public function getComment(): ?string
  {
    return $this->comment;
  }

  public function setComment(?string $comment): self
  {
    $this->comment = $comment;

    return $this;
  }

  public function getLatitude(): ?string
  {
    return $this->latitude;
  }

  public function setLatitude(?string $latitude): self
  {
    $this->latitude = $latitude;

    return $this;
  }

  public function getLongitude(): ?string
  {
    return $this->longitude;
  }

  public function setLongitude(?string $longitude): self
  {
    $this->longitude = $longitude;

    return $this;
  }

  /**
   * Update existing Entity from self.
   *
   * @param Dealer $target
   *
   * @return Dealer
   */
  public function update(Dealer $target): Dealer
  {
    $target->setAddress1($this->getAddress1());
    $target->setAddress2($this->getAddress2());
    $target->setCity($this->getCity());
    $target->setComment($this->getComment());
    $target->setCountry($this->getCountry());
    $target->setEmail($this->getEmail());
    $target->setEnabled($this->getEnabled());
    $target->setFax($this->getFax());
    $target->setLatitude($this->getLatitude());
    $target->setLongitude($this->getLongitude());
    $target->setName($this->getName());
    $target->setPhone($this->getPhone());
    $target->setPostalCode($this->getPostalCode());
    $target->setSlug($this->getSlug());
    $target->setState($this->getState());
    $target->setType($this->getType());
    $target->setUrl($this->getUrl());
    $target->setWholesale($this->getWholesale());

    return $target;
  }
}
