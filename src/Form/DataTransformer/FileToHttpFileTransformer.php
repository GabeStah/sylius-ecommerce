<?php

namespace App\Form\DataTransformer;

use App\Entity\File\File;
use App\Entity\Product\ProductFile;
use App\Service\Uploader\FileUploader;
use App\Service\Uploader\FileUploaderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileToHttpFileTransformer implements DataTransformerInterface
{
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;
  /**
   * @var FileUploaderInterface
   */
  private $fileUploader;

  public function __construct(
    EntityManagerInterface $entityManager,
    FileUploader $fileUploader
  ) {
    $this->entityManager = $entityManager;
    $this->fileUploader = $fileUploader;
  }

  /**
   * Transform an HttpFile to a File entity.
   *
   * @param ProductFile[]|null $files
   *
   * @return ArrayCollection
   */
  public function transform($files)
  {
    if (!$files) {
      return null;
    }

    $httpFiles = [];

    foreach ($files as $file) {
      if (!$file->hasFile()) {
        // causes a validation error
        // this message is not shown to the user
        // see the invalid_message option
        throw new TransformationFailedException(
          sprintf('The File does not have an underlying HttpFile.')
        );
      } else {
        $fileEntity = $file->getFile();
        $fileEntity->hydrate();

        $httpFiles[] = $fileEntity->getFile();
      }
    }

    return new ArrayCollection($httpFiles);
  }

  /**
   * Transform an File entity to an HttpFile.
   *
   * @param UploadedFile[]|null $uploadedFiles
   *
   * @return ArrayCollection
   * @throws TransformationFailedException if file is not found.
   */
  public function reverseTransform($uploadedFiles)
  {
    if (!$uploadedFiles) {
      return null;
    }

    $files = [];

    foreach ($uploadedFiles as $uploadedFile) {
      $path = $uploadedFile->getPathname();
      $checksum = hash_file('sha256', $path);

      $existingFile = $this->entityManager
        ->getRepository(File::class)
        ->findOneBy(['checksum' => $checksum]);

      if ($existingFile) {
        $files[] = $existingFile;
      } else {
        $path = $this->fileUploader->upload($uploadedFile);
        $file = new File();
        $file->setFile(new HttpFile($path));
        $file->hydrate();

        $files[] = $file;
      }
    }

    return new ArrayCollection($files);
  }
}
