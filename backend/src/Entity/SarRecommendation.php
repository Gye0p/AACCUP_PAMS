<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
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

    #[ORM\Column(length: 20)]
    #[Groups(['sar:read', 'sar:write', 'assignment:read'])]
    private string $priority = 'medium';

    public function getId(): ?int { return $this->id; }

    public function getAreaAssignment(): ?AreaAssignment { return $this->areaAssignment; }
    public function setAreaAssignment(?AreaAssignment $areaAssignment): static { $this->areaAssignment = $areaAssignment; return $this; }

    public function getRecommendationText(): ?string { return $this->recommendationText; }
    public function setRecommendationText(string $recommendationText): static { $this->recommendationText = $recommendationText; return $this; }

    public function getPriority(): string { return $this->priority; }
    public function setPriority(string $priority): static { $this->priority = $priority; return $this; }
}
