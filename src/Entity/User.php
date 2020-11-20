<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
    public const PATREON_NONE = 0;
    public const PATREON_BRONZE = 1;
    public const PATREON_SILVER = 2;
    public const PATREON_GOLD = 3;
    public const PATREON_RUBY = 4;

    public const PATREON = [
        self::PATREON_NONE => 'None',
        self::PATREON_BRONZE => 'Bronze',
        self::PATREON_SILVER => 'Silver',
        self::PATREON_GOLD => 'Gold',
        self::PATREON_RUBY => 'Ruby',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $username = '';

    /**
     * @ORM\Column(type="string", length=40)
=     */
    private string $discordDiscriminator = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $avatar = 'unknown';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $discordId = '';

    /**
     * @OneToMany(targetEntity="DiscordGuild", mappedBy="owner")
     */
    private Collection $discordGuilds;

    /**
     * @ORM\OneToMany(targetEntity="GuildMembership", mappedBy="user")
     */
    private Collection $guildMemberships;

    /**
     * @ORM\OneToMany(targetEntity="EventAttendee", mappedBy="user")
     */
    private Collection $events;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\Positive()
     */
    private int $clock = 24;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private string $timezone = 'UTC';

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotNull()
     */
    private bool $darkmode = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $discordToken = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $discordRefreshToken = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $discordTokenExpirationDate = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $patreonMembership = 0;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\PositiveOrZero()
     * 1 defaults to Monday, 0 is Sunday
     */
    private int $firstDayOfWeek = 1;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CharacterPreset", mappedBy="user", orphanRemoval=true)
     * @OrderBy({"name" = "ASC"})
     */
    private Collection $characterPresets;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $icalId = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->guildMemberships = new ArrayCollection();
        $this->discordGuilds = new ArrayCollection();
        $this->characterPresets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->username.'#'.$this->discordDiscriminator;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
        return;
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

    public function getDiscordDiscriminator(): string
    {
        return $this->discordDiscriminator;
    }

    public function setDiscordDiscriminator(string $discordDiscriminator): self
    {
        $this->discordDiscriminator = $discordDiscriminator;

        return $this;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function getFullAvatarUrl(): string
    {
        if (null !== $this->avatar && 'unknown' !== $this->avatar) {
            return 'https://cdn.discordapp.com/avatars/'.$this->discordId.'/'.$this->avatar.'.png';
        }

        return '/build/images/default_avatar.png';
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getDarkmode(): bool
    {
        return $this->darkmode;
    }

    public function setDarkmode(bool $darkmode): self
    {
        $this->darkmode = $darkmode;

        return $this;
    }

    public function getDiscordToken(): ?string
    {
        return $this->discordToken;
    }

    public function setDiscordToken(?string $discordToken): User
    {
        $this->discordToken = $discordToken;

        return $this;
    }

    public function getDiscordRefreshToken(): ?string
    {
        return $this->discordRefreshToken;
    }

    public function setDiscordRefreshToken(?string $discordRefreshToken): User
    {
        $this->discordRefreshToken = $discordRefreshToken;

        return $this;
    }

    /**
     * @return Collection|DiscordGuild[]
     */
    public function getDiscordGuilds(): Collection
    {
        return $this->discordGuilds;
    }

    /**
     * @return Collection|DiscordGuild[]
     */
    public function getActiveDiscordGuilds(): Collection
    {
        return $this->discordGuilds->filter(static function (DiscordGuild $discordGuild) {
            return $discordGuild->isActive();
        });
    }

    public function setDiscordGuilds(Collection $discordGuilds): self
    {
        $this->discordGuilds = $discordGuilds;

        return $this;
    }

    /**
     * @return Collection|GuildMembership[]
     */
    public function getGuildMemberships(): Collection
    {
        return $this->guildMemberships;
    }

    /**
     * @return Collection|GuildMembership[]
     */
    public function getActiveGuildMemberships(): Collection
    {
        $active = $this->guildMemberships->filter(static function (GuildMembership $guildMembership) {
            return $guildMembership->getGuild()->isActive();
        });
        $iterator = $active->getIterator();
        $iterator->uasort(static function ($a, $b) {
            return strcmp(strtolower($a->getGuild()->getName()), strtolower($b->getGuild()->getName()));
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function getGuildNickname(DiscordGuild $guild): string
    {
        $active = $this->guildMemberships->filter(static function (GuildMembership $guildMembership) use ($guild) {
            return $guildMembership->getGuild()->getId() === $guild->getId();
        });

        if (1 > $active->count()) {
            return $this->getUsername();
        }

        return $active->first()->getNickname();
    }

    public function setGuildMemberships(Collection $guildMemberships): self
    {
        $this->guildMemberships = $guildMemberships;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function setEvents(Collection $events): self
    {
        $this->events = $events;

        return $this;
    }

    public function getClock(): int
    {
        return $this->clock;
    }

    public function setClock(int $clock): self
    {
        $this->clock = $clock;

        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function toUserTimeString(\DateTimeInterface $dateTime): string
    {
        $dateTime->setTimezone(new DateTimeZone($this->timezone ?? 'UTC'));

        if (12 === $this->clock) {
            return $dateTime->format('F jS g:ia');
        }

        return $dateTime->format('F jS H:i');
    }

    public function toUserTime(DateTimeInterface $dateTime): DateTimeInterface
    {
        return $dateTime->setTimezone(new DateTimeZone($this->timezone ?? 'UTC'));
    }

    public function getDiscordMention(): string
    {
        return '<@'.$this->getDiscordId().'>';
    }

    public function getDiscordTokenExpirationDate(): ?DateTimeInterface
    {
        return $this->discordTokenExpirationDate;
    }

    public function setDiscordTokenExpirationDate(DateTimeInterface $discordTokenExpirationDate): self
    {
        $this->discordTokenExpirationDate = $discordTokenExpirationDate;

        return $this;
    }

    public function getPatreonMembership(): int
    {
        return $this->patreonMembership;
    }

    public function setPatreonMembership(int $patreonMembership): self
    {
        $this->patreonMembership = $patreonMembership;

        return $this;
    }

    public function getFirstDayOfWeek(): int
    {
        return $this->firstDayOfWeek;
    }

    public function setFirstDayOfWeek(int $firstDayOfWeek): self
    {
        $this->firstDayOfWeek = $firstDayOfWeek;

        return $this;
    }

    /**
     * @return Collection|CharacterPreset[]
     */
    public function getCharacterPresets(): Collection
    {
        return $this->characterPresets;
    }

    public function addCharacterPreset(CharacterPreset $characterPreset): self
    {
        if (!$this->characterPresets->contains($characterPreset)) {
            $this->characterPresets[] = $characterPreset;
            $characterPreset->setUser($this);
        }

        return $this;
    }

    public function removeCharacterPreset(CharacterPreset $characterPreset): self
    {
        if ($this->characterPresets->contains($characterPreset)) {
            $this->characterPresets->removeElement($characterPreset);
            // set the owning side to null (unless already changed)
            if ($characterPreset->getUser() === $this) {
                $characterPreset->setUser(null);
            }
        }

        return $this;
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

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function generateIcalId(): void
    {
        if (null === $this->getIcalId()) {
            $this->setIcalId(bin2hex(random_bytes(20)));
        }
    }
}
