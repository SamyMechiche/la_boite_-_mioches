<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $beginning_hour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $ending_hour = null;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'activities')]
    private Collection $GroupId;

    public function __construct()
    {
        $this->GroupId = new ArrayCollection();
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

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBeginningHour(): ?\DateTime
    {
        return $this->beginning_hour;
    }

    public function setBeginningHour(\DateTime $beginning_hour): static
    {
        $this->beginning_hour = $beginning_hour;

        return $this;
    }

    public function getEndingHour(): ?\DateTime
    {
        return $this->ending_hour;
    }

    public function setEndingHour(\DateTime $ending_hour): static
    {
        $this->ending_hour = $ending_hour;

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroupId(): Collection
    {
        return $this->GroupId;
    }

    public function addGroupId(Group $groupId): static
    {
        if (!$this->GroupId->contains($groupId)) {
            $this->GroupId->add($groupId);
        }

        return $this;
    }

    public function removeGroupId(Group $groupId): static
    {
        $this->GroupId->removeElement($groupId);

        return $this;
    }
}
