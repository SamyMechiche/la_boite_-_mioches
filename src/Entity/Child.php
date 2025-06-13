<?php

namespace App\Entity;

use App\Repository\ChildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChildRepository::class)]
class Child
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $fname = null;

    #[ORM\Column(length: 100)]
    private ?string $lname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $birth_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $signing_date = null;

    #[ORM\ManyToOne(inversedBy: 'children')]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id")]
    private ?User $parent = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'Child')]
    private Collection $users;

    /**
     * @var Collection<int, ExternalContact>
     */
    #[ORM\ManyToMany(targetEntity: ExternalContact::class, inversedBy: 'children')]
    private Collection $ExternalContact;

    #[ORM\Column(length: 50)]
    private ?string $relation_type = null;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'child')]
    private Collection $Attendance;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->ExternalContact = new ArrayCollection();
        $this->Attendance = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFname(): ?string
    {
        return $this->fname;
    }

    public function setFname(string $fname): static
    {
        $this->fname = $fname;

        return $this;
    }

    public function getLname(): ?string
    {
        return $this->lname;
    }

    public function setLname(string $lname): static
    {
        $this->lname = $lname;

        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birth_date;
    }

    public function setBirthDate(\DateTime $birth_date): static
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;

        return $this;
    }

    public function getSigningDate(): ?\DateTime
    {
        return $this->signing_date;
    }

    public function setSigningDate(\DateTime $signing_date): static
    {
        $this->signing_date = $signing_date;

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): static
    {
        $this->group = $group;
        return $this;
    }

    public function getParent(): ?User
    {
        return $this->parent;
    }

    public function setParent(?User $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addChild($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeChild($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ExternalContact>
     */
    public function getExternalContact(): Collection
    {
        return $this->ExternalContact;
    }

    public function addExternalContact(ExternalContact $externalContact): static
    {
        if (!$this->ExternalContact->contains($externalContact)) {
            $this->ExternalContact->add($externalContact);
        }

        return $this;
    }

    public function removeExternalContact(ExternalContact $externalContact): static
    {
        $this->ExternalContact->removeElement($externalContact);

        return $this;
    }

    public function getRelationType(): ?string
    {
        return $this->relation_type;
    }

    public function setRelationType(string $relation_type): static
    {
        $this->relation_type = $relation_type;

        return $this;
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendance(): Collection
    {
        return $this->Attendance;
    }

    public function addAttendance(Attendance $attendance): static
    {
        if (!$this->Attendance->contains($attendance)) {
            $this->Attendance->add($attendance);
            $attendance->setChild($this);
        }

        return $this;
    }

    public function removeAttendance(Attendance $attendance): static
    {
        if ($this->Attendance->removeElement($attendance)) {
            // set the owning side to null (unless already changed)
            if ($attendance->getChild() === $this) {
                $attendance->setChild(null);
            }
        }

        return $this;
    }
}
