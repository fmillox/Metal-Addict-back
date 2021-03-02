<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class MyValidator
{
  private $validator;
  
  public function __construct(ValidatorInterface $validator)
  {
    $this->validator = $validator;
  }

  public function validate($entity, $constraints = null, $groups = null): array
  {
    $errorsArray = [];
    $errors = $this->validator->validate($entity, $constraints, $groups);
    for ($i = 0; $i < $errors->count(); $i++) { 
      $errorsArray[$errors[$i]->getPropertyPath()] = $errors[$i]->getMessageTemplate();
    }

    return $errorsArray;
  }
}
