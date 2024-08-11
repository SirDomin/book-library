<?php

namespace App\Manager;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

class FileManager
{
    private string $uploadDirectory;

    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    public function __construct(string $uploadDirectory)
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->createDirectoryIfNotExists();
    }

    private function createDirectoryIfNotExists(): void
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->uploadDirectory)) {
            try {
                $filesystem->mkdir($this->uploadDirectory, 0777);
            } catch (IOExceptionInterface $exception) {
                throw new \RuntimeException(sprintf('An error occurred while creating the directory "%s".', $this->uploadDirectory));
            }
        }
    }

    public function saveFile(string $fileEncoded, string $extension): string
    {
        if (!in_array(strtolower($extension), $this->allowedExtensions)) {
            throw new \Exception(\sprintf('File extension %s not supported', $extension));
        }

        $imageContent = base64_decode($fileEncoded);

        if (false === $imageContent) {
            throw new \Exception('Invalid image data');
        }

        $fileName = uniqid().'.'.$extension;
        $filePath = $this->uploadDirectory.'/'.$fileName;

        file_put_contents($filePath, $imageContent);

        return $fileName;
    }

    public function getFileContent(string $fileName): Response
    {
        $filePath = $this->uploadDirectory.'/'.$fileName;

        if (!file_exists($filePath)) {
            throw new \RuntimeException('File not found.');
        }

        $mimeType = mime_content_type($filePath);
        $fileContent = file_get_contents($filePath);

        return new Response($fileContent, Response::HTTP_OK, [
            'Content-Type' => $mimeType,
        ]);
    }
}
