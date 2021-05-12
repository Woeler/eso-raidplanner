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
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventAttendeeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EventAttendee
{
    use HasEsoClass;
    use HasEsoRole;

    public const STATUS_ATTENDING = 1;
    public const STATUS_RESERVE = 2;
    public const STATUS_CONFIRMED = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="events")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private User $user;

    /**
     * @ManyToOne(targetEntity="Event", inversedBy="attendees")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private Event $event;

    /**
     * @ORM\Column(type="integer")
     */
    private int $status = self::STATUS_ATTENDING;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $class = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $role = null;

    /**
     * @ORM\ManyToMany(targetEntity="ArmorSet")
     */
    private Collection $sets;

    private ?CharacterPreset $preset;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CharacterPreset")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?CharacterPreset $characterPreset = null;

    public function __construct()
    {
        $this->sets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function setSets(array $sets): self
    {
        $this->sets = new ArrayCollection($sets);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatusEmoji(): string
    {
        if (self::STATUS_CONFIRMED === $this->status) {
            return '✅';
        }
        if (self::STATUS_RESERVE === $this->status) {
            return '🟡';
        }

        return '';
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        // Set created at if record is first persisted
        if (null === $this->getCreatedAt()) {
            $this->setCreatedAt(new DateTime('now'));
        }

        $this->setUpdatedAt(new DateTime('now'));
    }

    public function __toString(): string
    {
        return $this->user->getUsername().'#'.$this->user->getDiscordDiscriminator();
    }

    public function getCharacterPreset(): ?CharacterPreset
    {
        return $this->characterPreset;
    }

    public function setCharacterPreset(?CharacterPreset $characterPreset): self
    {
        $this->characterPreset = $characterPreset;

        return $this;
    }
}
