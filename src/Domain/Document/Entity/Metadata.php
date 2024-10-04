<?php

declare(strict_types=1);

namespace App\Domain\Document\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'document_metadata')]
#[HasLifecycleCallbacks]
class Metadata
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    private ?int $id = null;

    #[OneToOne(inversedBy: 'metadata')]
    #[JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: false)]
    private Document $document;

    #[Column(name: 'size', type: 'string')]
    private string $size;

    #[Column(name: 'type', type: 'string', length: 255)]
    private string $type;

    #[Column(name: 'tags', type: 'json', nullable: true)]
    private ?array $tags = null;

    #[Column(name: 'created_at', type: 'datetime')]
    private DateTime $created_at;

    #[Column(name: 'updated_at', type: 'datetime')]
    private DateTime $updated_at;



    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Domain\Document\Entity\Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @param \App\Domain\Document\Entity\Document $document
     * @return \App\Domain\Document\Entity\Metadata
     */
    public function setDocument(Document $document): self
    {
        $this->document = $document;
        if ($document->getMetadata() !== $this) {
            $document->setMetadata($this);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @param string $size
     * @return \App\Domain\Document\Entity\Metadata
     */
    public function setSize(string $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return \App\Domain\Document\Entity\Metadata
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     * @return \App\Domain\Document\Entity\Metadata
     */
    public function setTags(?array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }


    /**
     * Summary of getCreatedAt
     * @return \DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updated_at;
    }

    #[PrePersist]
    /**
     * @return void
     */
    public function setCreatedAt(): void
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
    public function setUpdatedAt(): void
    {
        $this->updated_at = new DateTime();
    }
}
