<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\CollegeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CollegeRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Put(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Patch(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Delete(security: "is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['college:read']],
    denormalizationContext: ['groups' => ['college:write']],
)]
class College
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['college:read', 'program:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Groups(['college:read', 'college:write', 'program:read', 'user:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Groups(['college:read', 'college:write', 'program:read', 'user:read'])]
    private ?string $code = null;

    #[ORM\OneToMany(targetEntity: Program::class, mappedBy: 'college')]
    #[Groups(['college:read'])]
    private Collection $programs;

    public function __construct()
    {
        $this->programs = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }

    public function getPrograms(): Collection { return $this->programs; }
    public function addProgram(Program $program): static
    {
        if (!$this->programs->contains($program)) {
            $this->programs->add($program);
            $program->setCollege($this);
        }
        return $this;
    }
    public function removeProgram(Program $program): static
    {
        if ($this->programs->removeElement($program)) {
            if ($program->getCollege() === $this) {
                $program->setCollege(null);
            }
        }
        return $this;
    }
}
