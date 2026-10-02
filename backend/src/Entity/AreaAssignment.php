<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Repository\AreaAssignmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AreaAssignmentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Patch(
            uriTemplate: '/area-assignments/{id}/ia-review',
            security: "is_granted('ROLE_INTERNAL_ACCREDITOR') or is_granted('ROLE_QUAMC_ADMIN')",
            denormalizationContext: ['groups' => ['ia_review:write']],
        ),
    ],
    normalizationContext: ['groups' => ['assignment:read']],
    denormalizationContext: ['groups' => ['assignment:write']],
)]
class AreaAssignment
{
    public const IA_STATUS_PENDING = 'pending';
    public const IA_STATUS_APPROVED = 'approved';
    public const IA_STATUS_NEEDS_REVISION = 'needs_revision';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['assignment:read', 'cycle:read', 'activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AccreditationCycle::class, inversedBy: 'areaAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['assignment:read'])]
    private ?AccreditationCycle $cycle = null;

    #[ORM\ManyToOne(targetEntity: AaccupArea::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['assignment:read', 'cycle:read'])]
    private ?AaccupArea $area = null;

    #[ORM\Column(length: 30)]
    #[Groups(['assignment:read', 'ia_review:write'])]
    private string $iaStatus = self::IA_STATUS_PENDING;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['assignment:read', 'ia_review:write'])]
    private ?string $iaComments = null;

    #[ORM\OneToMany(targetEntity: ComplianceActivity::class, mappedBy: 'areaAssignment')]
    #[Groups(['assignment:read'])]
    private Collection $complianceActivities;

    #[ORM\OneToMany(targetEntity: SarRecommendation::class, mappedBy: 'areaAssignment')]
    #[Groups(['assignment:read'])]
    private Collection $sarRecommendations;

    public function __construct()
    {
        $this->complianceActivities = new ArrayCollection();
        $this->sarRecommendations = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getCycle(): ?AccreditationCycle { return $this->cycle; }
    public function setCycle(?AccreditationCycle $cycle): static { $this->cycle = $cycle; return $this; }

    public function getArea(): ?AaccupArea { return $this->area; }
    public function setArea(?AaccupArea $area): static { $this->area = $area; return $this; }

    public function getIaStatus(): string { return $this->iaStatus; }
    public function setIaStatus(string $iaStatus): static { $this->iaStatus = $iaStatus; return $this; }

    public function getIaComments(): ?string { return $this->iaComments; }
    public function setIaComments(?string $iaComments): static { $this->iaComments = $iaComments; return $this; }

    public function getComplianceActivities(): Collection { return $this->complianceActivities; }

    public function getSarRecommendations(): Collection { return $this->sarRecommendations; }
}
