<?php

namespace App\Entity\Listener;

use App\Entity\File\File;
use App\Repository\FileRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class FileUniqueConstraintListener
{
  /**
   * @var FileRepository
   */
  protected $repository;

  public function __construct(FileRepository $repository)
  {
    $this->repository = $repository;
  }

  public function prePersist(File $file, LifecycleEventArgs $event): ?File
  {
    return $this->repository->findOneBy(['checksum', $file->getChecksum()]);
  }
}
