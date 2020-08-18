<?php declare(strict_types=1);

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
     */
    private ?string $id = null;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="discordGuilds")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true)
     */
    private User $owner;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $icon = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name = '';

    /**
     * @ORM\OneToMany(targetEntity="GuildMembership", mappedBy="guild", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    private Collection $members;

    /**
     * @ORM\OneToMany(targetEntity="DiscordChannel", mappedBy="guild")
     * @OrderBy({"name" = "ASC"})
     */
    private Collection $discordChannels;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $active = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $botActive = false;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="guild")
     */
    private Collection $events;

    /**
     * @ORM\OneToMany(targetEntity="Reminder", mappedBy="guild")
     */
    private Collection $reminders;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\DiscordChannel")
     * @ORM\JoinColumn(name="log_channel", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?DiscordChannel $logChannel;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecurringEvent", mappedBy="guild", orphanRemoval=true)
     */
    private Collection $recurringEvents;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\DiscordChannel")
     * @ORM\JoinColumn(name="event_create_channel", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?DiscordChannel $eventCreateChannel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $icalId = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->discordChannels = new ArrayCollection();
        $this->reminders = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->recurringEvents = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->getDiscordId();
    }

    public function getDiscordId(): string
    {
        return $this->id;
    }

    public function setDiscordId(string $discordId): self
    {
        $this->id = $discordId;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getFullIconUrl(): string
    {
        if (null !== $this->icon) {
            return 'https://cdn.discordapp.com/icons/'.$this->id.'/'.$this->icon.'.png';
        }

        return '/build/images/default_avatar.png';
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getBotActive(): bool
    {
        return $this->botActive;
    }

    public function setBotActive(bool $botActive): self
    {
        $this->botActive = $botActive;

        return $this;
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function getMembers(): Collection
    {
        $iterator = $this->members->getIterator();
        $iterator->uasort(static function ($a, $b) {
            return strcmp(strtolower($a->getUser()->getUsername()), strtolower($b->getUser()->getUsername()));
        });

        return new ArrayCollection(iterator_to_array($iterator));
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

    public function getAdmins(bool $excludeOwner = false): Collection
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

    public function getLogChannel(): ?DiscordChannel
    {
        return $this->logChannel;
    }

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

    public function getEventCreateChannel(): ?DiscordChannel
    {
        return $this->eventCreateChannel;
    }

    public function setEventCreateChannel(?DiscordChannel $eventCreateChannel): self
    {
        $this->eventCreateChannel = $eventCreateChannel;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getIcalId(): ?string
    {
        return $this->icalId;
    }

    public function setIcalId(?string $icalId): self
    {
        $this->icalId = $icalId;

        return $this;
    }

    public function generateIcalId(): void
    {
        if (null === $this->getIcalId()) {
            $this->setIcalId(bin2hex(random_bytes(20)));
        }
    }
}
