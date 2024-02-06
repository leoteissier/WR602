<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\HasLifecycleCallbacks] // This annotation is used to call the setCreatedAt() method before the entity is persisted and updated.
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * @var array<string>
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $subcriptionEndAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Subscription $subcriptionId = null;

    /** @var Collection<int, Pdf> */
    #[ORM\OneToMany(targetEntity: Pdf::class, mappedBy: 'userId')]
    private Collection $pdfs;

    public function __construct()
    {
        $this->pdfs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getSubcriptionEndAt(): ?\DateTimeInterface
    {
        return $this->subcriptionEndAt;
    }

    public function setSubcriptionEndAt(?\DateTimeInterface $subcriptionEndAt): static
    {
        $this->subcriptionEndAt = $subcriptionEndAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist] // This annotation is used to call the setCreatedAt() method before the entity is persisted.
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    #[ORM\PreUpdate] // This annotation is used to call the setUpdatedAt() method before the entity is updated.
    public function setUpdateAt(?\DateTimeInterface $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getSubcriptionId(): ?Subscription
    {
        return $this->subcriptionId;
    }

    public function setSubcriptionId(?Subscription $subcriptionId): static
    {
        $this->subcriptionId = $subcriptionId;

        return $this;
    }

    /**
     * @return Collection<int, Pdf>
     */
    public function getPdfs(): Collection
    {
        return $this->pdfs;
    }

    public function addPdf(Pdf $pdf): static
    {
        if (!$this->pdfs->contains($pdf)) {
            $this->pdfs->add($pdf);
            $pdf->setUserId($this);
        }

        return $this;
    }

    public function removePdf(Pdf $pdf): static
    {
        if ($this->pdfs->removeElement($pdf)) {
            // set the owning side to null (unless already changed)
            if ($pdf->getUserId() === $this) {
                $pdf->setUserId(null);
            }
        }

        return $this;
    }
}
