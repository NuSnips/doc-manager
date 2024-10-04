<?php

declare(strict_types=1);

namespace App\Domain\DocumentShare\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'document_downloads')]
class DocumentShareDownload
{

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    private ?int $id;

    #[OneToOne(targetEntity: DocumentShare::class, inversedBy: 'documentShareDownload')]
    private ?DocumentShare $documentShare;

    #[Column(name: 'count', type: 'integer')]
    private int $count = 0;

    #[Column(name: 'created_at', type: 'datetime')]
    private DateTime $created_at;

    #[Column(name: 'updated_at', type: 'datetime')]
    private DateTime $updated_at;

    public function __construct(DocumentShare $documentShare)
    {
        $this->documentShare = $documentShare;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocumentShare(): ?DocumentShare
    {
        return $this->documentShare;
    }

    public function setDocumentShare(?DocumentShare $documentShare): void
    {
        $this->documentShare = $documentShare;
    }

    public function getCount(): int
    {
        return $this->count;
    }
    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function incrementCount()
    {
        $this->count++;
    }

    #[PrePersist]
    /**
     * @return void
     */
    public function setCreatedAt()
    {
        if (!isset($this->created_at)) {
            $this->created_at = new DateTime();
        }
    }
    #[PrePersist]
    #[PreUpdate]
    /**
     * @return void
     */
    public function setUpdatedAt()
    {
        $this->updated_at = new DateTime();
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}
