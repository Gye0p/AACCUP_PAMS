<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\AaccupAreaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AaccupAreaRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['area:read']],
)]
class AaccupArea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['area:read', 'assignment:read', 'activity:read', 'ia_assignment:read'])]
    private ?int $id = null;

    /** Roman numeral area number: I through X */
    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Groups(['area:read', 'assignment:read', 'activity:read', 'ia_assignment:read'])]
    private ?string $areaNumber = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Groups(['area:read', 'assignment:read', 'activity:read', 'ia_assignment:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['area:read'])]
    private ?string $description = null;

    public function getId(): ?int { return $this->id; }

    public function getAreaNumber(): ?string { return $this->areaNumber; }
    public function setAreaNumber(string $areaNumber): static { $this->areaNumber = $areaNumber; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
}
