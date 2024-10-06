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

    public function search(string $searchTerm): array
    {
        $params = [
            'index' => $this->index,
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            // Full match query on 'name' and 'path'
                            [
                                'multi_match' => [
                                    'query' => $searchTerm,
                                    'fields' => ['name', 'path'],
                                    'type' => 'best_fields' // (default is 'best_fields')
                                ]
                            ],
                            // Partial match query on 'name' and 'path'
                            [
                                'wildcard' => [
                                    'name' => '*' . $searchTerm . '*', // Partial match for 'name'
                                ]
                            ],
                            [
                                'wildcard' => [
                                    'path' => '*' . $searchTerm . '*', // Partial match for 'path'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            $documents = $response['hits']['hits'];
            return array_map(
                fn($doc) =>
                $doc['_id']
                // 'source' => $doc['_source'], 
                ,
                $documents
            );
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }
}
