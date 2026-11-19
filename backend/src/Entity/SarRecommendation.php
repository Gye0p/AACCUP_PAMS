<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\SarRecommendationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SarRecommendationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Patch(
            security: "is_granted('ROLE_QUAMC_ADMIN') or is_granted('ROLE_AREA_COORDINATOR') or is_granted('ROLE_PROGRAM_HEAD')",
            denormalizationContext: ['groups' => ['sar:compliance:write']],
        ),
        new Delete(security: "is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['sar:read']],
    denormalizationContext: ['groups' => ['sar:write']],
)]
class SarRecommendation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['sar:read', 'assignment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AreaAssignment::class, inversedBy: 'sarRecommendations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['sar:read', 'sar:write'])]
    private ?AreaAssignment $areaAssignment = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Groups(['sar:read', 'sar:write', 'assignment:read'])]
    private ?string $recommendationText = null;

    /** mandatory or enhancement */
    #[ORM\Column(length: 20)]
    #[Groups(['sar:read', 'sar:write', 'assignment:read'])]
    private string $type = 'mandatory';

    #[ORM\Column(length: 20)]
    #[Groups(['sar:read', 'sar:write', 'assignment:read'])]
    private string $priority = 'medium';

    /** Evidence links or document references to demonstrate compliance */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['sar:read', 'sar:write', 'sar:compliance:write', 'assignment:read'])]
    private ?string $complianceDocuments = null;

    /** COMPLIED | PARTIAL | NA — set manually by Area Coordinator or Program Head */
    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['sar:read', 'sar:write', 'sar:compliance:write', 'assignment:read'])]
    private ?string $remarks = null;

    public function getId(): ?int { return $this->id; }

    public function getAreaAssignment(): ?AreaAssignment { return $this->areaAssignment; }
    public function setAreaAssignment(?AreaAssignment $areaAssignment): static { $this->areaAssignment = $areaAssignment; return $this; }

    public function getRecommendationText(): ?string { return $this->recommendationText; }
    public function setRecommendationText(string $recommendationText): static { $this->recommendationText = $recommendationText; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getPriority(): string { return $this->priority; }
    public function setPriority(string $priority): static { $this->priority = $priority; return $this; }

    public function getComplianceDocuments(): ?string { return $this->complianceDocuments; }
    public function setComplianceDocuments(?string $complianceDocuments): static { $this->complianceDocuments = $complianceDocuments; return $this; }

    public function getRemarks(): ?string { return $this->remarks; }
    public function setRemarks(?string $remarks): static { $this->remarks = $remarks; return $this; }
}
