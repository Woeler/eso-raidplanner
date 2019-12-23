<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use App\Entity\Traits\HasEsoClass;
use App\Entity\Traits\HasEsoRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacterPresetRepository")
 */
class CharacterPreset
{
    use HasEsoRole, HasEsoClass;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $class;

    /**
     * @ORM\Column(type="integer")
     */
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ArmorSet")
     */
    private $sets;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="characterPresets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->sets = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getClass(): ?int
    {
        return $this->class;
    }

    public function setClass(int $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection|ArmorSet[]
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }

    public function addSet(ArmorSet $set): self
    {
        if (!$this->sets->contains($set)) {
            $this->sets[] = $set;
        }

        return $this;
    }

    public function removeSet(ArmorSet $set): self
    {
        if ($this->sets->contains($set)) {
            $this->sets->removeElement($set);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
