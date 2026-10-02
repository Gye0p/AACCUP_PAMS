<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\Repository\InternalAccreditorAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InternalAccreditorAssignmentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_QUAMC_ADMIN') or is_granted('ROLE_INTERNAL_ACCREDITOR')"),
        new Get(security: "is_granted('ROLE_QUAMC_ADMIN') or is_granted('ROLE_INTERNAL_ACCREDITOR')"),
        new Post(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Delete(security: "is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['ia_assignment:read']],
    denormalizationContext: ['groups' => ['ia_assignment:write']],
)]
class InternalAccreditorAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ia_assignment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['ia_assignment:read', 'ia_assignment:write'])]
    private ?User $internalAccreditor = null;

    #[ORM\ManyToOne(targetEntity: AreaAssignment::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['ia_assignment:read', 'ia_assignment:write'])]
    private ?AreaAssignment $areaAssignment = null;

    public function getId(): ?int { return $this->id; }

    public function getInternalAccreditor(): ?User { return $this->internalAccreditor; }
    public function setInternalAccreditor(?User $internalAccreditor): static { $this->internalAccreditor = $internalAccreditor; return $this; }

    public function getAreaAssignment(): ?AreaAssignment { return $this->areaAssignment; }
    public function setAreaAssignment(?AreaAssignment $areaAssignment): static { $this->areaAssignment = $areaAssignment; return $this; }
}
