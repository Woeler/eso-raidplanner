<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArmorSetRepository")
 */
class ArmorSet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $slug;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $esoHubId = null;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): ArmorSet
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ArmorSet
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): ArmorSet
    {
        $this->slug = $slug;

        return $this;
    }

    public function getEsoHubId(): ?int
    {
        return $this->esoHubId;
    }

    public function setEsoHubId(?int $esoHubId): ArmorSet
    {
        $this->esoHubId = $esoHubId;

        return $this;
    }
}
