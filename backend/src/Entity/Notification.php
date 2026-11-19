<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ApiResource(
    operations: [
        // Each user only sees their own notifications (enforced by DataScopeExtension)
        new GetCollection(security: "is_granted('IS_AUTHENTICATED_FULLY')"),
        new Patch(
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            denormalizationContext: ['groups' => ['notification:mark_read']],
        ),
    ],
    normalizationContext: ['groups' => ['notification:read']],
    denormalizationContext: ['groups' => ['notification:write']],
    order: ['createdAt' => 'DESC'],
)]
class Notification
{
    public const TYPE_CYCLE_CREATED = 'cycle_created';
    public const TYPE_SAR_ENTERED = 'sar_entered';
    public const TYPE_ACTIVITY_SUBMITTED = 'activity_submitted';
    public const TYPE_REVISION_REQUESTED = 'revision_requested';
    public const TYPE_ACTIVITY_APPROVED = 'activity_approved';
    public const TYPE_AREA_APPROVED = 'area_approved';
    public const TYPE_DEADLINE_REMINDER = 'deadline_reminder';
    public const TYPE_OVERDUE = 'overdue';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notification:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['notification:read'])]
    private ?User $recipient = null;

    #[ORM\Column(length: 50)]
    #[Groups(['notification:read'])]
    private string $type = '';

    #[ORM\Column(type: 'text')]
    #[Groups(['notification:read'])]
    private string $message = '';

    /** URL path to the related item, e.g. /cycle/42 */
    #[ORM\Column(length: 300, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $link = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['notification:read', 'notification:mark_read'])]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['notification:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getRecipient(): ?User { return $this->recipient; }
    public function setRecipient(?User $recipient): static { $this->recipient = $recipient; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): static { $this->message = $message; return $this; }

    public function getLink(): ?string { return $this->link; }
    public function setLink(?string $link): static { $this->link = $link; return $this; }

    public function getReadAt(): ?\DateTimeImmutable { return $this->readAt; }
    public function setReadAt(?\DateTimeImmutable $readAt): static { $this->readAt = $readAt; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function isRead(): bool { return $this->readAt !== null; }
}
