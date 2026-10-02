<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\Repository\EvidenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EvidenceRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            security: "is_granted('ROLE_PROGRAM_HEAD') or is_granted('ROLE_QUAMC_ADMIN')",
            securityPostDenormalize: "is_granted('ROLE_QUAMC_ADMIN') or (is_granted('ROLE_PROGRAM_HEAD') and object.getActivity().getAreaAssignment().getCycle().getProgram() == user.getProgram())",
            inputFormats: ['multipart' => ['multipart/form-data']],
        ),
        new Delete(security: "is_granted('ROLE_PROGRAM_HEAD') or is_granted('ROLE_QUAMC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['evidence:read']],
    denormalizationContext: ['groups' => ['evidence:write']],
)]
class Evidence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['evidence:read', 'activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ComplianceActivity::class, inversedBy: 'evidences')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['evidence:read', 'evidence:write'])]
    private ?ComplianceActivity $activity = null;

    #[Vich\UploadableField(mapping: 'evidence_files', fileNameProperty: 'fileName', size: 'fileSize', mimeType: 'mimeType')]
    #[Assert\NotNull(groups: ['evidence:create'])]
    #[Assert\File(
        maxSize: '20M',
        mimeTypes: [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ],
        mimeTypesMessage: 'Only PDF, JPEG, PNG, Word, Excel, and PowerPoint files are allowed.'
    )]
    #[Groups(['evidence:write'])]
    private ?File $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['evidence:read', 'activity:read'])]
    private ?string $fileName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['evidence:read'])]
    private ?int $fileSize = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['evidence:read'])]
    private ?string $mimeType = null;

    #[ORM\Column(length: 300, nullable: true)]
    #[Groups(['evidence:read', 'evidence:write'])]
    private ?string $originalName = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['evidence:read'])]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['evidence:read'])]
    private ?User $uploadedBy = null;

    public function getId(): ?int { return $this->id; }

    public function getActivity(): ?ComplianceActivity { return $this->activity; }
    public function setActivity(?ComplianceActivity $activity): static { $this->activity = $activity; return $this; }

    public function getFile(): ?File { return $this->file; }
    public function setFile(?File $file): static
    {
        $this->file = $file;
        if ($file !== null) {
            $this->uploadedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getFileName(): ?string { return $this->fileName; }
    public function setFileName(?string $fileName): static { $this->fileName = $fileName; return $this; }

    public function getFileSize(): ?int { return $this->fileSize; }
    public function setFileSize(?int $fileSize): static { $this->fileSize = $fileSize; return $this; }

    public function getMimeType(): ?string { return $this->mimeType; }
    public function setMimeType(?string $mimeType): static { $this->mimeType = $mimeType; return $this; }

    public function getOriginalName(): ?string { return $this->originalName; }
    public function setOriginalName(?string $originalName): static { $this->originalName = $originalName; return $this; }

    public function getUploadedAt(): ?\DateTimeImmutable { return $this->uploadedAt; }
    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static { $this->uploadedAt = $uploadedAt; return $this; }

    public function getUploadedBy(): ?User { return $this->uploadedBy; }
    public function setUploadedBy(?User $uploadedBy): static { $this->uploadedBy = $uploadedBy; return $this; }
}
