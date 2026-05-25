<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'projects')]
#[ORM\HasLifecycleCallbacks]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: ProjectStatus::class)]
    #[ORM\JoinColumn(name: 'status_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?ProjectStatus $status = null;

    #[ORM\Column(name: 'planned_start_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $plannedStartDate = null;

    #[ORM\Column(name: 'planned_end_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $plannedEndDate = null;

    #[ORM\Column(name: 'actual_end_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $actualEndDate = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectMember::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $members;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', nullable: true)]
    private ?User $updatedBy = null;

    #[ORM\Column(name: 'created_at')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'deleted_at', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): ?ProjectStatus
    {
        return $this->status;
    }

    public function setStatus(?ProjectStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPlannedStartDate(): ?\DateTimeInterface
    {
        return $this->plannedStartDate;
    }

    public function setPlannedStartDate(?\DateTimeInterface $plannedStartDate): static
    {
        $this->plannedStartDate = $plannedStartDate;
        return $this;
    }

    public function getPlannedEndDate(): ?\DateTimeInterface
    {
        return $this->plannedEndDate;
    }

    public function setPlannedEndDate(?\DateTimeInterface $plannedEndDate): static
    {
        $this->plannedEndDate = $plannedEndDate;
        return $this;
    }

    public function getActualEndDate(): ?\DateTimeInterface
    {
        return $this->actualEndDate;
    }

    public function setActualEndDate(?\DateTimeInterface $actualEndDate): static
    {
        $this->actualEndDate = $actualEndDate;
        return $this;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(ProjectMember $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setProject($this);
        }
        return $this;
    }

    public function removeMember(ProjectMember $member): static
    {
        if ($this->members->removeElement($member)) {
            if ($member->getProject() === $this) {
                $member->setProject(null);
            }
        }
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
}