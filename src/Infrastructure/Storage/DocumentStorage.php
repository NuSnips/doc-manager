<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\Document\Entity\Document;
use Exception;
use Slim\Psr7\UploadedFile;


class DocumentStorage
{
    private string $baseStoragePath;

    public function __construct()
    {
        $this->baseStoragePath = public_path($_ENV['UPLOAD_DIR']);
        if (!is_dir($this->baseStoragePath)) {
            mkdir($this->baseStoragePath, 0755, true);
        }
    }
    public function saveUploadedFile(string $fileName, UploadedFile $uploadedFile, ?string $folderPath = null): bool
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $this->getUploadErrorMessage($uploadedFile->getError()));
        }

        $fullPath = $this->getFullPath($fileName, $folderPath);
        $uploadedFile->moveTo($fullPath);
        return true;
    }

    public function deleteFile(string $fileName, ?string $folderPath = null): bool
    {
        $fullPath = $this->baseStoragePath . "/" . $folderPath . "/" . $fileName;

        if ($this->fileExists($fileName, $folderPath)) {
            unlink($fullPath);
            return true;
        }
        return false;
    }

    private function fileExists(string $fileName, string $folderPath): bool
    {
        $fullPath = $this->baseStoragePath . "/" . $folderPath . "/" . $fileName;
        return file_exists($fullPath);
    }

    /**
     * @param string $fileName
     * @param mixed $folderPath
     * @return string
     */
    public function getFullPath(string $fileName, ?string $folderPath = null): string
    {
        // Base storage path
        $path = $this->baseStoragePath;
        // If a folder path is provided, append it to the base path
        if ($folderPath !== null) {
            // Trim any leading or trailing slashes from the folder path
            $folderPath = trim($folderPath, '/');
            if ($folderPath !== '') {
                $path .= '/' . $folderPath;
            }
        }
        // Append the file name to the path
        return $path . '/' . $fileName;
    }

    public function createFolder(string $folderName)
    {
        // Check if folder exists.
        $folderExists = is_dir($this->baseStoragePath . '/' . $folderName);
        if (!$folderExists) {
            mkdir($this->baseStoragePath . '/' . $folderName, 0775, true);
        }
    }

    public function download(Document $document)
    {
        $folderPath = explode(DIRECTORY_SEPARATOR, $document->getPath())[0];
        $filePath = $this->baseStoragePath . "/" . $folderPath . "/" . $document->getName();
        if (file_exists($filePath)) {

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
