<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueChecksum extends Constraint
{
  public $message = 'This file already exists.';

  public function validatedBy(): string
  {
    return static::class . 'Validator';
  }
}
