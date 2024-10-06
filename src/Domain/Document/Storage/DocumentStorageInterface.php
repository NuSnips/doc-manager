<?php

namespace App\Domain\Document\Storage;

use App\Domain\Document\Entity\Document;
use Psr\Http\Message\UploadedFileInterface;

interface DocumentStorageInterface
{
    /**
     * SAve an uploaded file
     * @param string $fileName
     * @param UploadedFileInterface $uploadedFile
     * @param mixed $folderPath
     * @return void
     */
    public function saveUploadedFile(string $fileName, UploadedFileInterface $uploadedFile, ?string $folderPath = null): void;

    /**
     * Delete a file
     * @param string $fileName
     * @param mixed $folderPath
     * @return void
     */
    public function deleteFile(string $fileName, ?string $folderPath = null): void;

    /**
     * Check if file exists
     * @param string $fileName
     * @param mixed $folderPath
     * @return bool
     */
    public function fileExists(string $fileName, ?string $folderPath = null): bool;

    /**
     * Set full path for file
     * @param string $fileName
     * @param mixed $folderPath
     * @return string
     */
    public function getFullPath(string $fileName, ?string $folderPath = null): string;

    /**
     * Create a folder
     * @param string $folderName
     * @return void
     */
    public function createFolder(string $folderName): void;

    /**
     * Download a document
     * @param Document $document
     * @return void
     */
    public function downloadDocument(Document $document): void;
}
