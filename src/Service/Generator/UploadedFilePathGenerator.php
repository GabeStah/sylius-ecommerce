<?php

declare(strict_types=1);

namespace App\Service\Generator;

use App\Entity\File\FileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

final class UploadedFilePathGenerator implements FilePathGeneratorInterface
{
  /**
   * @var SluggerInterface
   */
  private $slugger;

  /**
   * UploadedFilePathGenerator constructor.
   */
  public function __construct()
  {
    $this->slugger = new AsciiSlugger();
  }

  /**
   * @param string $path
   *
   * @return string
   */
  private function expandPath(string $path): string
  {
    return sprintf(
      '%s/%s/%s',
      substr($path, 0, 2),
      substr($path, 2, 2),
      substr($path, 4)
    );
  }

  /**
   * @param FileInterface $file
   *
   * @return string
   */
  public function fromFile(FileInterface $file): string
  {
    return $this->fromUploadedFile($file->getFile());
  }

  /**
   * @param UploadedFile $uploadedFile
   *
   * @return string
   */
  public function fromUploadedFile(UploadedFile $uploadedFile): string
  {
    $checksum = hash_file('sha256', $uploadedFile->getPathname());

    $originalFilename = pathinfo(
      $uploadedFile->getClientOriginalName(),
      PATHINFO_FILENAME
    );
    $safeFilename = $this->slugger->slug($originalFilename);
    return $safeFilename .
      '-' .
      substr($checksum, 0, 8) .
      '.' .
      $uploadedFile->guessExtension();
  }
}
