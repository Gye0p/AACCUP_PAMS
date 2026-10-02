<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\MonitoringReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MonitoringReportRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_QUAMC_ADMIN') or is_granted('ROLE_INTERNAL_ACCREDITOR')"),
    ],
    normalizationContext: ['groups' => ['report:read']],
    denormalizationContext: ['groups' => ['report:write']],
)]
class MonitoringReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['report:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AccreditationCycle::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['report:read', 'report:write'])]
    private ?AccreditationCycle $cycle = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['report:read'])]
    private ?User $generatedBy = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['report:read'])]
    private ?\DateTimeImmutable $generatedAt = null;

    #[ORM\Column(length: 20)]
    #[Groups(['report:read', 'report:write'])]
    private string $reportType = 'pdf';

    public function __construct()
    {
        $this->generatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getCycle(): ?AccreditationCycle { return $this->cycle; }
    public function setCycle(?AccreditationCycle $cycle): static { $this->cycle = $cycle; return $this; }

    public function getGeneratedBy(): ?User { return $this->generatedBy; }
    public function setGeneratedBy(?User $generatedBy): static { $this->generatedBy = $generatedBy; return $this; }

    public function getGeneratedAt(): ?\DateTimeImmutable { return $this->generatedAt; }

    public function getReportType(): string { return $this->reportType; }
    public function setReportType(string $reportType): static { $this->reportType = $reportType; return $this; }
}
