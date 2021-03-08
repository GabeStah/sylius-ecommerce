<?php

namespace App\EventListener;

use App\Entity\File\File;
use App\Repository\FileRepository;
use App\Service\Uploader\FileUploader;
use App\Service\Uploader\FileUploaderInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

class FileListener
{
  const MESSAGE_FILE_EXISTS = 'File already exists.';
  const TYPE_FILE_EXISTS = 'FILE_EXISTS';

  /**
   * @var FileRepository
   */
  private $fileRepository;

  /**
   * @var FileUploaderInterface
   */
  private $fileUploader;

  public function __construct(
    FileRepository $fileRepository,
    FileUploader $fileUploader
  ) {
    $this->fileRepository = $fileRepository;
    $this->fileUploader = $fileUploader;
  }

  /**
   * Check for existing file via checksum.
   *
   * @param ResourceControllerEvent $event
   */
  public function onPostDelete(ResourceControllerEvent $event)
  {
    /** @var File $file */
    $file = $event->getSubject();

    // Remove file
    $removed = $this->fileUploader->remove($file->getPath());
  }

  /**
   * Check for existing file via checksum.
   *
   * @param ResourceControllerEvent $event
   */
  public function onPreCreate(ResourceControllerEvent $event)
  {
    /** @var File $file */
    $file = $event->getSubject();
    $file->updateChecksum();

    $existingFile = $this->fileRepository->findOneByChecksum(
      $file->getChecksum()
    );

    if ($existingFile) {
      $event->stop(static::MESSAGE_FILE_EXISTS);
      $event->setMessageType(static::TYPE_FILE_EXISTS);
    }
  }
}
