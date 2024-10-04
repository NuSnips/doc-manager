<?php

declare(strict_types=1);

namespace App\Application\Action;

use App\Application\Service\DocumentService;
use App\Domain\User\Entity\User;
use App\Infrastructure\Storage\DocumentStorage;
use Exception;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

class CreateDocument
{

    public function __construct(private DocumentService $documentService) {}
    public function execute(array $uploadedFiles, User $user, array $tags = [])
    {
        $documentStorage = new DocumentStorage();

        $email = $user->getEmail();
        // Save the uploaded file
        if ($uploadedFiles) {
            $uploadedFile = $uploadedFiles['document'];
            $fileName = str_replace(' ', '_', basename($uploadedFile->getClientFilename()));;
            try {
                $fileUploaded = $documentStorage->saveUploadedFile($fileName, $uploadedFile,  $email);

                $data = [];
                $filePath = $user->getEmail() . DIRECTORY_SEPARATOR . $fileName;
                $fileType = $uploadedFile->getClientMediaType();
                $fileSize = $uploadedFile->getSize(); // bytes
                $data['name'] = $fileName;
                $data['path'] = $filePath;
                $data['type'] = $fileType;
                $data['size'] = $fileSize . "";
                $data['user'] = $user;
                $data['tags'] = $tags ?? '';
                $document = $this->documentService->createDocument($data);
                return true;
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }
}
