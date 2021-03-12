<?php

declare(strict_types=1);

namespace App\Service\Uploader;

use App\Entity\File\FileInterface;
use App\Service\Generator\FilePathGeneratorInterface;
use App\Service\Generator\UploadedFilePathGenerator;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader implements FileUploaderInterface
{
  /** @var FilePathGeneratorInterface */
  protected $filePathGenerator;
  /**
   * @var SluggerInterface
   */
  private $slugger;
  /**
   * @var ContainerBagInterface
   */
  private $containerBag;

  /**
   * FileUploader constructor.
   *
   * @param SluggerInterface                $slugger
   * @param ContainerBagInterface           $containerBag
   * @param FilePathGeneratorInterface|null $filePathGenerator
   */
  public function __construct(
    SluggerInterface $slugger,
    ContainerBagInterface $containerBag,
    ?FilePathGeneratorInterface $filePathGenerator = null
  ) {
    $this->slugger = $slugger;
    $this->containerBag = $containerBag;

    $this->filePathGenerator =
      $filePathGenerator ?? new UploadedFilePathGenerator();
  }

  /**
   * Remove file from filesystem.
   *
   * @param string $path
   *
   * @return bool
   */
  public function remove(string $path): bool
  {
    return unlink($path);
  }

  /**
   * @param FileInterface $file
   *
   * @return string|null
   */
  public function handleRemoteFile(FileInterface $file): ?string
  {
    $file->hydrate();
    return $file->getUrl();
  }

  /**
   * Upload file to filesystem.
   *
   * Accepts either resource event with FileInterface subject or Uploaded File object
   *
   * @param ResourceControllerEvent|UploadedFile $subject
   *
   * @return string|null
   */
  public function upload($subject): ?string
  {
    try {
      $file = null;
      $uploadedFile = null;
      if ($subject instanceof ResourceControllerEvent) {
        /** @var FileInterface $file */
        $file = $subject->getSubject();
        if (!$file->hasFile()) {
          return $this->handleRemoteFile($file);
        }
        $path = $this->filePathGenerator->fromFile($file);
        $uploadedFile = $file->getFile();
      } else {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $subject;
        $path = $this->filePathGenerator->fromUploadedFile($subject);
      }

      $newFile = null;

      $newFile = $uploadedFile->move(
        // Total hack because dependency injection continues to ignore service definition
        // that explicitly passes `%upload_directory%` parameter to FileUploader service.
        $this->containerBag->get('kernel.project_dir') . '/public/uploads',
        $path
      );

      if ($subject instanceof ResourceControllerEvent) {
        $file->setFile($newFile);
        $file->hydrate();
      }

      return $newFile->getPathname();
    } catch (FileException $e) {
      throw $e;
    }
  }
}
