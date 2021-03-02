<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
  private $targetDirectory;
  
  public function __construct($targetDirectory)
  {
    $this->targetDirectory = $targetDirectory;
  }

  public function upload(UploadedFile $file, string $directory)
  {
    $fileDirectory = $this->targetDirectory . '/' . $directory;
    $fileDirectory = str_replace('///', '/', $fileDirectory);
    $fileDirectory = str_replace('//', '/', $fileDirectory);
    if (!str_ends_with($fileDirectory, '/')) {
      $fileDirectory .= '/';
    }
    $fileDirectory .= date_format(new \DateTime(), 'Y/m/d') . '/';
    $fileName = uniqid() . '.' . $file->guessExtension();

    try {
      $file->move($fileDirectory, $fileName);
    } catch (FileException $e) {
      // ... handle exception if something happens during file upload
    }

    $key = '/public';
    $fileDirectory = substr($fileDirectory, strpos($fileDirectory, $key) + strlen($key));

    return $fileDirectory . $fileName;
  }
}
