<?php

namespace App\Entity;

use App\Repository\PdfRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks] // This annotation is used to call the setCreatedAt() method before the entity is persisted and updated.
#[ORM\Entity(repositoryClass: PdfRepository::class)]
#[ORM\Table(name: '`pdf`')]
class Pdf
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'pdfs')]
    private ?User $user_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist] // This annotation is used to call the setCreatedAt() method before the entity is persisted.
    public function setCreatedAt(): static
    {
        if ($this->createdAt === null) { // Ne définir createdAt que s'il est null
            $this->createdAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }
}
