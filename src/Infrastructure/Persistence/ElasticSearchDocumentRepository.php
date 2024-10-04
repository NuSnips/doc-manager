<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Document\Entity\Document;
use Elastic\Elasticsearch\Client;
use Exception;

class ElasticSearchDocumentRepository
{

    public function __construct(private Client $client) {}

    private string $index = 'documents';
    public function index(Document $document): void
    {

        $params = [
            'index' => $this->index,
            'id'    => $document->getId(),
            'body'  => [
                'name'    => $document->getName(),
                'path'    => $document->getPath(),
                'user_id' => $document->getUser()->getId(),
            ]
        ];
        try {
            $this->client->index($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
