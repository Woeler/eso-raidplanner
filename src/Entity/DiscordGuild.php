<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DiscordGuildRepository")
 */
class DiscordGuild
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     * @var string
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="discordGuilds")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true)
     * @var User
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $icon;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="GuildMembership", mappedBy="guild", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var Collection|GuildMembership[]
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity="DiscordChannel", mappedBy="guild")
     * @OrderBy({"name" = "ASC"})
     * @var Collection|DiscordChannel[]
     */
    private $discordChannels;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $active;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $botActive;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="guild")
     * @var Collection|Event[]
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="Reminder", mappedBy="guild")
     * @var Collection|Reminder[]
     */
    private $reminders;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\DiscordChannel")
     * @ORM\JoinColumn(name="log_channel", referencedColumnName="id", nullable=true)
     * @var DiscordChannel
     */
    private $logChannel;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecurringEvent", mappedBy="guild", orphanRemoval=true)
     */
    private $recurringEvents;

    public function __construct()
    {
        $this->active = false;
        $this->botActive = false;
        $this->members = new ArrayCollection();
        $this->discordChannels = new ArrayCollection();
        $this->reminders = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->recurringEvents = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->getDiscordId();
    }

    /**
     * @return mixed
     */
    public function getDiscordId()
    {
        return $this->id;
    }

    /**
     * @param mixed $discordId
     * @return DiscordGuild
     */
    public function setDiscordId($discordId)
    {
        $this->id = $discordId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     * @return DiscordGuild
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     * @return DiscordGuild
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return DiscordGuild
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return DiscordGuild
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBotActive()
    {
        return $this->botActive;
    }

    /**
     * @param mixed $botActive
     * @return DiscordGuild
     */
    public function setBotActive($botActive)
    {
        $this->botActive = $botActive;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function isMember(User $user): bool
    {
        $u = $this->getMembers()->filter(function (GuildMembership $guildMembership) use ($user) {
            return $guildMembership->getUser()->getDiscordId() === $user->getDiscordId() && GuildMembership::ROLE_BANNED !== $guildMembership->getRole();
        });

        return 0 !== count($u);
    }

    public function addMember(User $user): self
    {
        if (!$this->isMember($user)) {
            $membership = (new GuildMembership())
                ->setUser($user)
                ->setGuild($this);
            $this->members->add($membership);
        }

        return $this;
    }

    public function makeAdmin(User $user): self
    {
        if ($this->isMember($user)) {
            $u = $this->getMembers()->filter(function (GuildMembership $guildMembership) use ($user) {
                return $guildMembership->getUser()->getDiscordId() === $user->getDiscordId();
            });
            $u->first()->setRole(GuildMembership::ROLE_ADMIN);
        }

        return $this;
    }

    public function isAdmin(User $user): bool
    {
        $u = $this->getMembers()->filter(function (GuildMembership $guildMembership) use ($user) {
            return ($guildMembership->getUser()->getDiscordId() === $user->getDiscordId() &&
                GuildMembership::ROLE_ADMIN === $guildMembership->getRole());
        });

        return 0 !== count($u);
    }

    public function getAdmins($excludeOwner = false): Collection
    {
        $u = $this->getMembers()->filter(function (GuildMembership $guildMembership) use ($excludeOwner) {
            if (!$excludeOwner) {
                return GuildMembership::ROLE_ADMIN === $guildMembership->getRole();
            } else {
                return GuildMembership::ROLE_ADMIN === $guildMembership->getRole() && $guildMembership->getGuild()->getOwner()->getId() !== $guildMembership->getUser()->getId();
            }
        });

        return $u;
    }

    /**
     * @return Collection
     */
    public function getDiscordChannels(): Collection
    {
        return $this->discordChannels;
    }

    /**
     * @return Reminder[]|Collection
     */
    public function getReminders()
    {
        return $this->reminders;
    }

    /**
     * @return DiscordChannel
     */
    public function getLogChannel(): ?DiscordChannel
    {
        return $this->logChannel;
    }

    /**
     * @param DiscordChannel $logChannel
     * @return DiscordGuild
     */
    public function setLogChannel(?DiscordChannel $logChannel): DiscordGuild
    {
        $this->logChannel = $logChannel;

        return $this;
    }

    /**
     * @return Collection|RecurringEvent[]
     */
    public function getRecurringEvents(): Collection
    {
        return $this->recurringEvents;
    }

    public function addRecurringEvent(RecurringEvent $recurringEvent): self
    {
        if (!$this->recurringEvents->contains($recurringEvent)) {
            $this->recurringEvents[] = $recurringEvent;
            $recurringEvent->setGuild($this);
        }

        return $this;
    }

    public function removeRecurringEvent(RecurringEvent $recurringEvent): self
    {
        if ($this->recurringEvents->contains($recurringEvent)) {
            $this->recurringEvents->removeElement($recurringEvent);
            // set the owning side to null (unless already changed)
            if ($recurringEvent->getGuild() === $this) {
                $recurringEvent->setGuild(null);
            }
        }

        return $this;
    }
}
