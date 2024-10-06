<?php

namespace App\Infrastructure\Storage;

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Storage\DocumentStorageInterface;
use Exception;
use Psr\Http\Message\UploadedFileInterface;

class DocumentStorage implements DocumentStorageInterface
{


    public function __construct(private string $baseStoragePath)
    {
        $this->ensureDirectoryExists($this->baseStoragePath);
    }
    public function saveUploadedFile(string $fileName, UploadedFileInterface $uploadedFile, ?string $folderPath = null): void
    {
        $this->validateUploadedFile($uploadedFile);
        $fullPath = $this->getFullPath($fileName, $folderPath);

        $uploadedFile->moveTo($fullPath);
    }

    public function deleteFile(string $fileName, ?string $folderPath = null): void
    {
        $fullPath = $this->getFullPath($fileName, $folderPath);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        } else {
            throw new Exception("File not found: $fullPath");
        }
    }

    public function fileExists(string $fileName, ?string $folderPath = null): bool
    {
        return false;
    }

    public function getFullPath(string $fileName, ?string $folderPath = null): string
    {
        $path = $this->baseStoragePath;
        if ($folderPath != null) {
            $path .= "/" . trim($folderPath);
        }
        return $path . "/" . $fileName;
    }

    public function createFolder(string $folderName): void
    {
        $folderPath = $this->baseStoragePath . "/" . $folderName;
        $this->ensureDirectoryExists($folderPath);
    }

    public function downloadDocument(Document $document): void
    {
        $filePath = $this->getFullPath($document->getName(), $document->getPath());

        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
        $this->outputFileForDownload($filePath);
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new Exception("Failed to create directory: $path");
        }
    }

    private function validateUploadedFile(UploadedFileInterface $uploadedFile): void
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $this->getUploadErrorMessage($uploadedFile->getError()));
        }
    }
    private function outputFileForDownload(string $filePath): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "File upload stopped by extension";
            default:
                return "Unknown upload error";
        }
    }
}
