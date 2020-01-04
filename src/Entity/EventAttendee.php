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
     * @var int
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="events")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var User
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="Event", inversedBy="attendees")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var Event
     */
    private $event;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $class;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity="ArmorSet")
     * @var Collection|ArmorSet[]
     */
    private $sets;

    private $preset;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $updatedAt;

    public function __construct()
    {
        $this->status = self::STATUS_ATTENDING;
        $this->sets = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return EventAttendee
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return EventAttendee
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     * @return EventAttendee
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return EventAttendee
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getClass(): ?int
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     * @return EventAttendee
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     * @return EventAttendee
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }

    /**
     * @param array $sets
     * @return EventAttendee
     */
    public function setSets(array $sets)
    {
        $this->sets = new ArrayCollection($sets);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return EventAttendee
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return EventAttendee
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return string
     */
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
}
