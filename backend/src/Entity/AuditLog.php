<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'audit_log')]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Get(security: "is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['audit:read']],
)]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['audit:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['audit:read'])]
    private ?User $actor = null;

    #[ORM\Column(length: 20)]
    #[Groups(['audit:read'])]
    private string $action;

    #[ORM\Column(length: 150)]
    #[Groups(['audit:read'])]
    private string $entityType;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['audit:read'])]
    private ?string $entityId = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['audit:read'])]
    private array $changes = [];

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['audit:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getActor(): ?User { return $this->actor; }
    public function setActor(?User $actor): static { $this->actor = $actor; return $this; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $action): static { $this->action = $action; return $this; }
    public function getEntityType(): string { return $this->entityType; }
    public function setEntityType(string $entityType): static { $this->entityType = $entityType; return $this; }
    public function getEntityId(): ?string { return $this->entityId; }
    public function setEntityId(?string $entityId): static { $this->entityId = $entityId; return $this; }
    public function getChanges(): array { return $this->changes; }
    public function setChanges(array $changes): static { $this->changes = $changes; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
