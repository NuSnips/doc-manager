<?php

declare(strict_types=1);

namespace App\Domain\DocumentShare\Entity;

use App\Domain\Document\Entity\Document;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'document_shares')]
#[HasLifecycleCallbacks]
class DocumentShare
{

    private string $prefix = "/documents/download/";
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Document::class, inversedBy: 'documentShares')]
    #[JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: false)]
    private Document $document;

    #[OneToOne(targetEntity: DocumentShareDownload::class, inversedBy: 'documentShare')]
    private ?DocumentShareDownload $documentShareDownload = null;

    #[Column(name: 'url', type: 'string', length: 255)]
    private string $url;

    #[Column(name: 'expires_at', type: 'datetime')]
    private DateTime $expires_at;

    #[Column(name: 'status', type: 'boolean')]
    private bool $status = true;

    #[Column(name: 'created_at', type: 'datetime')]
    private DateTime $created_at;

    #[Column(name: 'updated_at', type: 'datetime')]
    private DateTime $updated_at;

    public function getId()
    {
        return $this->id;
    }
    public function __construct(Document $document,  DateTime $expires_at)
    {
        $this->document = $document;
        $this->url = $this->generateUniqueUrlToken();
        $this->expires_at = $expires_at;
        $this->created_at = new DateTime();
    }

    private function generateUniqueUrlToken()
    {
        // Generate a unique value for the URL
        return  bin2hex(random_bytes(16));
    }
    /**
     * @return \App\Domain\Document\Entity\Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set shareable URL prefix
     * @param string $prefix
     * @return void
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }
    /**
     * Generate the shareable URL
     * @return string
     */
    public function generateUrl()
    {
        return "{$this->prefix}{$this->getUrl()}";
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt(): DateTime
    {
        return $this->expires_at;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param \DateTime $expires_at
     * @return \App\Domain\DocumentShare\Entity\DocumentShare
     */
    public function setExpiresAt(DateTime $expires_at): self
    {
        $this->expires_at = $expires_at;
        $this->setUpdatedAt();
        return $this;
    }
    public function setDocument(Document $document)
    {
        $this->document = $document;
        $this->setUpdatedAt();
        return $this;
    }

    public function getDocumentShareDownload(): ?DocumentShareDownload
    {
        return $this->documentShareDownload;
    }

    public function setDocumentShareDownload(?DocumentShareDownload $documentShareDownload): self
    {
        $this->documentShareDownload = $documentShareDownload;
        return $this;
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
