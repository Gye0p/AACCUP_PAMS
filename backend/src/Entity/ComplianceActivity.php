<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\ComplianceActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ComplianceActivityRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            security: "is_granted('ROLE_PROGRAM_HEAD') or is_granted('ROLE_QUAMC_ADMIN')",
            securityPostDenormalize: "is_granted('ROLE_QUAMC_ADMIN') or (is_granted('ROLE_PROGRAM_HEAD') and object.getAreaAssignment().getCycle().getProgram() == user.getProgram())",
        ),
        new Patch(
            security: "is_granted('ROLE_PROGRAM_HEAD') or is_granted('ROLE_QUAMC_ADMIN') or is_granted('ROLE_INTERNAL_ACCREDITOR')",
            securityPostDenormalize: "is_granted('ROLE_QUAMC_ADMIN') or (is_granted('ROLE_PROGRAM_HEAD') and object.getAreaAssignment().getCycle().getProgram() == user.getProgram()) or is_granted('ROLE_INTERNAL_ACCREDITOR')",
        ),
        new Delete(security: "is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['activity:read']],
    denormalizationContext: ['groups' => ['activity:write']],
)]
class ComplianceActivity
{
    public const STATE_DRAFT = 'draft';
    public const STATE_SUBMITTED = 'submitted';
    public const STATE_UNDER_REVIEW = 'under_review';
    public const STATE_NEEDS_REVISION = 'needs_revision';
    public const STATE_APPROVED = 'approved';
    public const STATE_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity:read', 'assignment:read', 'evidence:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AreaAssignment::class, inversedBy: 'complianceActivities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['activity:read', 'activity:write'])]
    private ?AreaAssignment $areaAssignment = null;

    #[ORM\Column(length: 300)]
    #[Assert\NotBlank]
    #[Groups(['activity:read', 'activity:write'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['activity:read', 'activity:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    #[Groups(['activity:read', 'activity:write'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    #[Groups(['activity:read', 'activity:write'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 30)]
    #[Groups(['activity:read'])]
    private string $state = self::STATE_DRAFT;

    /** Transition to apply: submit | start_review | request_revision | approve | complete */
    #[Groups(['activity:write'])]
    private ?string $transition = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['activity:read', 'activity:write'])]
    private ?string $iaComments = null;

    #[ORM\OneToMany(targetEntity: Evidence::class, mappedBy: 'activity', cascade: ['remove'])]
    #[Groups(['activity:read'])]
    private Collection $evidences;

    public function __construct()
    {
        $this->evidences = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getAreaAssignment(): ?AreaAssignment { return $this->areaAssignment; }
    public function setAreaAssignment(?AreaAssignment $areaAssignment): static { $this->areaAssignment = $areaAssignment; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getStartDate(): ?\DateTimeInterface { return $this->startDate; }
    public function setStartDate(\DateTimeInterface $startDate): static { $this->startDate = $startDate; return $this; }

    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function setEndDate(\DateTimeInterface $endDate): static { $this->endDate = $endDate; return $this; }

    public function getState(): string { return $this->state; }
    public function setState(string $state): static { $this->state = $state; return $this; }

    public function getTransition(): ?string { return $this->transition; }
    public function setTransition(?string $transition): static { $this->transition = $transition; return $this; }

    public function getIaComments(): ?string { return $this->iaComments; }
    public function setIaComments(?string $iaComments): static { $this->iaComments = $iaComments; return $this; }

    public function getEvidences(): Collection { return $this->evidences; }
}
