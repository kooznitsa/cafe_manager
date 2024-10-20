<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

class FileService
{
    public function __construct(
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    public function uploadFile(File $file, string $fileDirectory): string
    {
        $fileName = sprintf(
            '%s.%s',
            uniqid('image_', true),
            $file->getClientOriginalExtension(),
        );

        try {
            $file->move($fileDirectory, $fileName);
        } catch (FileException $e) {
            throw new FileException("File size exceeded.");
        }

        return $fileName;
    }

    public function removeFile(string $fileDirectory, string $imageName): void
    {
        $this->filesystem->remove("$fileDirectory/$imageName");
    }
}
