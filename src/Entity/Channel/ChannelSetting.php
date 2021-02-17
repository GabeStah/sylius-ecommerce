<?php

namespace App\Entity\Channel;

use App\Repository\ChannelSettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity(repositoryClass=ChannelSettingRepository::class)
 * @ORM\Table(name="sylius_channel_settings")
 */
class ChannelSetting implements ResourceInterface
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\Channel\Channel")
   * @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
   */
  private $channel;

  /**
   * @ORM\Column(type="string")
   * @var string
   */
  private $code;

  /**
   * @ORM\Column(type="boolean")
   * @var bool
   */
  private $enabled;

  /**
   * @ORM\Column(type="text")
   * @var string
   */
  private $value;

  public function getId(): ?int
  {
    return $this->id;
  }

  /**
   * @return Channel|null
   */
  public function getChannel(): ?Channel
  {
    return $this->channel;
  }

  /**
   * @param Channel $channel
   *
   * @return ChannelSetting
   */
  public function setChannel(Channel $channel): self
  {
    $this->channel = $channel;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getCode(): ?string
  {
    return $this->code;
  }

  /**
   * @param string $code
   *
   * @return ChannelSetting
   */
  public function setCode(string $code): self
  {
    $this->code = $code;
    return $this;
  }

  /**
   * @return bool|null
   */
  public function isEnabled(): ?bool
  {
    return $this->enabled;
  }

  /**
   * @param bool $enabled
   *
   * @return ChannelSetting
   */
  public function setEnabled(bool $enabled): self
  {
    $this->enabled = $enabled;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getValue(): ?string
  {
    return $this->value;
  }

  /**
   * @param $value
   *
   * @return ChannelSetting
   */
  public function setValue($value): self
  {
    $this->value = $value;
    return $this;
  }
}
