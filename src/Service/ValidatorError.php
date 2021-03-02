<?php

namespace App\Service;

use Symfony\Component\Form\FormErrorIterator;

class ValidatorError
{
  public function make(FormErrorIterator $formErrorIterator): array
  {
    $errorsArray = [];
    for ($i = 0; $i < $formErrorIterator->count(); $i++) { 
      $errorsArray[] = $formErrorIterator[$i]->getMessage();
    }

    return $errorsArray;
  }
}
