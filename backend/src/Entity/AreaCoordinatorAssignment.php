<?php
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\Repository\AreaCoordinatorAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AreaCoordinatorAssignmentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_QUAMC_ADMIN') or is_granted('ROLE_AREA_COORDINATOR')"),
        new Get(security: "is_granted('ROLE_QUAMC_ADMIN') or is_granted('ROLE_AREA_COORDINATOR')"),
        new Post(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Delete(security: "is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['ac_assignment:read']],
    denormalizationContext: ['groups' => ['ac_assignment:write']],
)]
class AreaCoordinatorAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ac_assignment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['ac_assignment:read', 'ac_assignment:write'])]
    private ?User $areaCoordinator = null;

    #[ORM\ManyToOne(targetEntity: AreaAssignment::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['ac_assignment:read', 'ac_assignment:write'])]
    private ?AreaAssignment $areaAssignment = null;

    public function getId(): ?int { return $this->id; }

    public function getAreaCoordinator(): ?User { return $this->areaCoordinator; }
    public function setAreaCoordinator(?User $areaCoordinator): static { $this->areaCoordinator = $areaCoordinator; return $this; }

    public function getAreaAssignment(): ?AreaAssignment { return $this->areaAssignment; }
    public function setAreaAssignment(?AreaAssignment $areaAssignment): static { $this->areaAssignment = $areaAssignment; return $this; }
}
