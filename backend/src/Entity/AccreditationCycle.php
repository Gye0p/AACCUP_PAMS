<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\Repository\AccreditationCycleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AccreditationCycleRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Patch(security: "is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['cycle:read']],
    denormalizationContext: ['groups' => ['cycle:write']],
)]
class AccreditationCycle
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PENDING = 'pending';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cycle:read', 'activity:read', 'assignment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Program::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['cycle:read', 'cycle:write'])]
    private ?Program $program = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['cycle:read', 'cycle:write', 'activity:read'])]
    private ?string $academicYear = null;

    /** Date NORSU received the SAR from AACCUP — used to compute compliance_deadline */
    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    #[Groups(['cycle:read', 'cycle:write'])]
    private ?\DateTimeInterface $sarReceivedDate = null;

    /** Locked at sarReceivedDate + 1 year. Never changeable. */
    #[ORM\Column(type: 'date')]
    #[Groups(['cycle:read'])]
    private ?\DateTimeInterface $complianceDeadline = null;

    #[ORM\Column(length: 20)]
    #[Groups(['cycle:read'])]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\OneToMany(targetEntity: AreaAssignment::class, mappedBy: 'cycle', cascade: ['persist'])]
    #[Groups(['cycle:read'])]
    private Collection $areaAssignments;

    public function __construct()
    {
        $this->areaAssignments = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getProgram(): ?Program { return $this->program; }
    public function setProgram(?Program $program): static { $this->program = $program; return $this; }

    public function getAcademicYear(): ?string { return $this->academicYear; }
    public function setAcademicYear(string $academicYear): static { $this->academicYear = $academicYear; return $this; }

    public function getSarReceivedDate(): ?\DateTimeInterface { return $this->sarReceivedDate; }
    public function setSarReceivedDate(\DateTimeInterface $sarReceivedDate): static
    {
        $this->sarReceivedDate = $sarReceivedDate;
        // Auto-compute compliance deadline: SAR date + 1 year (locked forever)
        $deadline = clone $sarReceivedDate;
        $deadline->modify('+1 year');
        $this->complianceDeadline = $deadline;
        return $this;
    }

    public function getComplianceDeadline(): ?\DateTimeInterface { return $this->complianceDeadline; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getAreaAssignments(): Collection { return $this->areaAssignments; }
    public function addAreaAssignment(AreaAssignment $aa): static
    {
        if (!$this->areaAssignments->contains($aa)) {
            $this->areaAssignments->add($aa);
            $aa->setCycle($this);
        }
        return $this;
    }
}
