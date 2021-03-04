<?php

declare(strict_types=1);

namespace App\Service\Generator;

use App\Entity\File\FileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FilePathGeneratorInterface
{
  /**
   * @param FileInterface $file
   *
   * @return string
   */
  public function fromFile(FileInterface $file): string;

  /**
   * @param UploadedFile $uploadedFile
   *
   * @return string
   */
  public function fromUploadedFile(UploadedFile $uploadedFile): string;
}
