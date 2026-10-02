<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\ProgramRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProgramRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Put(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Patch(security: "is_granted('ROLE_QUAMC_ADMIN')"),
        new Delete(security: "is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['program:read']],
    denormalizationContext: ['groups' => ['program:write']],
)]
class Program
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['program:read', 'user:read', 'cycle:read', 'activity:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Groups(['program:read', 'program:write', 'user:read', 'cycle:read', 'activity:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Groups(['program:read', 'program:write', 'user:read', 'cycle:read'])]
    private ?string $code = null;

    #[ORM\ManyToOne(targetEntity: College::class, inversedBy: 'programs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['program:read', 'program:write'])]
    private ?College $college = null;

    /** Accreditation level e.g. Level I, Level II, Level III, Level IV */
    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['program:read', 'program:write'])]
    private ?string $accreditationLevel = null;

    /** Date when current accreditation expires */
    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['program:read', 'program:write'])]
    private ?\DateTimeInterface $validityEnd = null;

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }

    public function getCollege(): ?College { return $this->college; }
    public function setCollege(?College $college): static { $this->college = $college; return $this; }

    public function getAccreditationLevel(): ?string { return $this->accreditationLevel; }
    public function setAccreditationLevel(?string $accreditationLevel): static { $this->accreditationLevel = $accreditationLevel; return $this; }

    public function getValidityEnd(): ?\DateTimeInterface { return $this->validityEnd; }
    public function setValidityEnd(?\DateTimeInterface $validityEnd): static { $this->validityEnd = $validityEnd; return $this; }
}
