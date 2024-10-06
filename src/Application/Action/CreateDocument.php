<?php

declare(strict_types=1);

namespace App\Application\Action;

use App\Application\Service\DocumentService;
use App\Domain\Document\Entity\Document;
use App\Domain\Document\Storage\DocumentStorageInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\Storage\DocumentStorage;
use Exception;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

class CreateDocument
{

    public function __construct(private DocumentService $documentService, private DocumentStorageInterface $documentStorage) {}
    public function execute(array $uploadedFiles, User $user, array $tags = []): ?Document
    {

        $email = $user->getEmail();
        // Check if uploadedFiles contains the document
        if (!isset($uploadedFiles['document']) || !$uploadedFiles['document'] instanceof UploadedFileInterface) {
            return null;
        }
        // Save the uploaded file
        $uploadedFile = $uploadedFiles['document'];
        $fileName = time() . "_" . $uploadedFile->getClientFilename();
        // $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);

        try {
            $this->documentStorage->saveUploadedFile($fileName, $uploadedFile,  $email);

            $data = [];
            $filePath = $user->getEmail();
            $fileType = $uploadedFile->getClientMediaType();
            $fileSize = $uploadedFile->getSize(); // bytes
            $data['name'] = $fileName;
            $data['path'] = $filePath;
            $data['type'] = $fileType;
            $data['size'] = $fileSize . "";
            $data['user'] = $user;
            $data['tags'] = $tags ?? '';
            return $this->documentService->createDocument($data);
        } catch (Exception $e) {
            throw new Exception('Error while creating document: ' . $e->getMessage());
        }
    }
}
