<?php

namespace App\Validator;

use App\Repository\FileRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class UniqueChecksumValidator extends ConstraintValidator
{
  /**
   * @var FileRepository
   */
  protected $repository;

  public function validate($value, Constraint $constraint)
  {
    if (!$constraint instanceof UniqueChecksum) {
      throw new UnexpectedTypeException($constraint, UniqueChecksum::class);
    }

    if (null === $value || '' === $value) {
      return;
    }
  }
}
