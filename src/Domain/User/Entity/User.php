<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\Document\Entity\Document;
use App\Domain\User\ValueObject\Fullname;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'users')]
#[HasLifecycleCallbacks]
class User
{

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    private ?int $id = null;


    #[OneToMany(targetEntity: Document::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $documents;

    #[Column(name: 'first_name', type: 'string', length: 255)]
    private string $first_name;

    #[Column(name: 'last_name', type: 'string', length: 255)]
    private string $last_name;

    #[Column(name: 'email', type: 'string', length: 255, unique: true)]
    private string $email;

    #[Column(name: 'password_hash', type: 'string', length: 255)]
    private string $password_hash;

    #[Column(name: 'created_at', type: 'datetime')]
    private DateTime $created_at;

    #[Column(name: 'updated_at', type: 'datetime')]
    private DateTime $updated_at;

    public function __construct(string $first_name, string $last_name, string $email, string $password)
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password_hash = $this->hashPassword($password);
        $this->created_at = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }
    public function getUpdatedAt(): DateTime
    {
        return $this->updated_at;
    }

    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPasswordHash(string $password): void
    {
        $this->password_hash = $this->hashPassword($password);
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setUser($this);
        }
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
