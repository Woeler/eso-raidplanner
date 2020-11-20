<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(min=1,max=200)
     */
    private string $name = '';

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     * @Assert\DateTime
     * @Assert\GreaterThan("yesterday")
     */
    private ?\DateTimeInterface $start = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max=2000)
     */
    private ?string $description = null;

    /**
     * @ManyToOne(targetEntity="DiscordGuild", inversedBy="events")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private DiscordGuild $guild;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $locked = false;

    /**
     * @ORM\Column(type="json")
     */
    private array $tags = [];

    /**
     * @ORM\OneToMany(targetEntity="EventAttendee", mappedBy="event")
     */
    private Collection $attendees;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RecurringEvent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?RecurringEvent $recurringParent = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="event", orphanRemoval=true)
     * @OrderBy({"createdAt" = "ASC"})
     */
    private Collection $comments;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\GreaterThan(propertyPath="start")
     */
    private ?\DateTimeInterface $end = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DiscordChannel")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?DiscordChannel $reminderRerouteChannel = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getGuild(): DiscordGuild
    {
        return $this->guild;
    }

    public function setGuild(DiscordGuild $guild): self
    {
        $this->guild = $guild;

        return $this;
    }

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return Collection|EventAttendee[]
     */
    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function isAttending(User $user): bool
    {
        $attending = $this->attendees->filter(static function (EventAttendee $attendee) use ($user) {
            return $attendee->getUser()->getId() === $user->getId();
        });

        return 0 < $attending->count();
    }

    /**
     * @param int $roleId
     * @return Collection|EventAttendee[]
     */
    public function getAttendeesByRole(int $roleId): Collection
    {
        return $this->attendees->filter(static function (EventAttendee $attendee) use ($roleId) {
            return $attendee->getRole() === $roleId;
        });
    }

    public function setAttendees(Collection $attendees): self
    {
        $this->attendees = $attendees;

        return $this;
    }

    public function getRecurringParent(): ?RecurringEvent
    {
        return $this->recurringParent;
    }

    public function setRecurringParent(?RecurringEvent $recurringParent): self
    {
        $this->recurringParent = $recurringParent;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setEvent($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getEvent() === $this) {
                $comment->setEvent(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name.' '.$this->start->format('Y-m-d H:i:s e');
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getReminderRerouteChannel(): ?DiscordChannel
    {
        return $this->reminderRerouteChannel;
    }

    public function setReminderRerouteChannel(?DiscordChannel $reminderRerouteChannel): self
    {
        $this->reminderRerouteChannel = $reminderRerouteChannel;

        return $this;
    }
}
