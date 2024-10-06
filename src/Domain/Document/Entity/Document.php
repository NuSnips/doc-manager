<?php

declare(strict_types=1);

namespace App\Domain\Document\Entity;

use App\Domain\DocumentShare\Entity\DocumentShare;
use App\Domain\User\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'documents')]
#[HasLifecycleCallbacks]
class Document
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    private ?int $id = null;

    #[Column(name: 'name', type: 'string', length: 255)]
    private string $name;

    #[Column(name: 'path', type: 'string', length: 255)]
    private string $path;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'documents')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[OneToMany(targetEntity: DocumentShare::class, mappedBy: 'document', cascade: ['persist', 'remove'])]
    private ?Collection $documentShares = null;

    #[OneToOne(targetEntity: Metadata::class, mappedBy: 'document', cascade: ['persist'])]
    private Metadata $metadata;

    #[Column(name: 'created_at', type: 'datetime')]
    private DateTime $created_at;

    #[Column(name: 'updated_at', type: 'datetime')]
    private DateTime $updated_at;

    public function __construct(string  $name, string $path, Metadata $metadata, User $user)
    {
        $this->name = $name;
        $this->path = $path;
        $this->metadata = $metadata;
        $this->metadata->setDocument($this);
        $this->user = $user;
        $this->created_at = new DateTime();
    }
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \App\Domain\User\Entity\User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updated_at;
    }
    public function getDocumentShares(): Collection
    {
        return $this->documentShares;
    }

    public function addDocumentShare(DocumentShare $documentShare): self
    {
        // if documentShares is null, initialize it
        if ($this->documentShares === null) {
            $this->documentShares = new ArrayCollection();
        }
        // check if the documentShare already exists in the collection (to avoid duplicates)
        if (!$this->documentShares->contains($documentShare)) {
            $this->documentShares->add($documentShare);
            $documentShare->setDocument($this);
        }
        return $this;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function setMetadata(Metadata $metadata): self
    {
        if ($this->metadata->getDocument() !== $this) {
            $metadata->setDocument($this);
        }
        $this->metadata = $metadata;
        return $this;
    }
    /**
     * @param string $name
     * @return \App\Domain\Document\Entity\Document
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $path
     * @return \App\Domain\Document\Entity\Document
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param \App\Domain\User\Entity\User $user
     * @return \App\Domain\Document\Entity\Document
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
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
}
