<?php

declare(strict_types=1);

namespace App\Service\Uploader;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

interface FileUploaderInterface
{
  public function upload(
    ResourceControllerEvent $subject
  ): ?string;

  public function remove(string $path): bool;
}
