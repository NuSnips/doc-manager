<?php

declare(strict_types=1);

namespace App\Application\Action;

use App\Application\Service\DocumentService;
use App\Domain\Document\Storage\DocumentStorageInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\Storage\DocumentStorage;
use Exception;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

class CreateDocument
{

    public function __construct(private DocumentService $documentService, private DocumentStorageInterface $documentStorage) {}
    public function execute(array $uploadedFiles, User $user, array $tags = [])
    {

        $email = $user->getEmail();
        // Save the uploaded file
        if ($uploadedFiles) {
            $uploadedFile = $uploadedFiles['document'];
            $fileName = time() . "_" . $uploadedFile->getClientFilename();
            $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
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
                $document = $this->documentService->createDocument($data);
                return true;
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }
}
