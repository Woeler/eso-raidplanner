<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=40)
     * @var string
     */
    private $discordDiscriminator;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $discordId;

    /**
     * @OneToMany(targetEntity="DiscordGuild", mappedBy="owner")
     * @var Collection|DiscordGuild[]
     */
    private $discordGuilds;

    /**
     * @ORM\OneToMany(targetEntity="GuildMembership", mappedBy="user")
     * @var Collection|GuildMembership[]
     */
    private $guildMemberships;

    /**
     * @ORM\OneToMany(targetEntity="EventAttendee", mappedBy="user")
     * @var Collection|EventAttendee[]
     */
    private $events;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $clock;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $timezone;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $darkmode;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $discordToken;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $discordRefreshToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private $discordTokenExpirationDate;

    public function __construct()
    {
        $this->clock = 24;
        $this->timezone = 'UTC';
        $this->events = new ArrayCollection();
        $this->guildMemberships = new ArrayCollection();
        $this->discordGuilds = new ArrayCollection();
        $this->darkmode = false;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * @return string|null The encoded password if any
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
        return;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordDiscriminator(): string
    {
        return $this->discordDiscriminator;
    }

    /**
     * @param string $discordDiscriminator
     * @return User
     */
    public function setDiscordDiscriminator(string $discordDiscriminator): self
    {
        $this->discordDiscriminator = $discordDiscriminator;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     * @return User
     */
    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    /**
     * @param string $discordId
     * @return User
     */
    public function setDiscordId(string $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDarkmode()
    {
        return $this->darkmode;
    }

    /**
     * @param mixed $darkmode
     * @return User
     */
    public function setDarkmode($darkmode)
    {
        $this->darkmode = $darkmode;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordToken(): string
    {
        return $this->discordToken;
    }

    /**
     * @param string $discordToken
     * @return User
     */
    public function setDiscordToken(string $discordToken): User
    {
        $this->discordToken = $discordToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordRefreshToken(): string
    {
        return $this->discordRefreshToken;
    }

    /**
     * @param string $discordRefreshToken
     * @return User
     */
    public function setDiscordRefreshToken(string $discordRefreshToken): User
    {
        $this->discordRefreshToken = $discordRefreshToken;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getDiscordGuilds(): Collection
    {
        return $this->discordGuilds;
    }

    /**
     * @return Collection
     */
    public function getActiveDiscordGuilds(): Collection
    {
        return $this->discordGuilds->filter(function (DiscordGuild $discordGuild) {
            return $discordGuild->isActive();
        });
    }

    /**
     * @param Collection $discordGuilds
     * @return User
     */
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
     * @return Collection
     */
    public function getActiveGuildMemberships(): Collection
    {
        return $this->guildMemberships->filter(function (GuildMembership $guildMembership) {
            return $guildMembership->getGuild()->isActive();
        });
    }

    /**
     * @param Collection $guildMemberships
     * @return User
     */
    public function setGuildMemberships(Collection $guildMemberships): self
    {
        $this->guildMemberships = $guildMemberships;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param mixed $events
     * @return User
     */
    public function setEvents($events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClock()
    {
        return $this->clock;
    }

    /**
     * @param mixed $clock
     * @return User
     */
    public function setClock($clock)
    {
        $this->clock = $clock;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param mixed $timezone
     * @return User
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @param DateTime $dateTime
     * @return string
     */
    public function toUserTimeString(DateTime $dateTime): string
    {
        $dateTime->setTimezone(new DateTimeZone($this->timezone ?? 'UTC'));

        if (12 === $this->clock) {
            return $dateTime->format('F jS g:ia');
        }

        return $dateTime->format('F jS H:i');
    }

    /**
     * @return string
     */
    public function getDiscordMention(): string
    {
        return '<@'.$this->getDiscordId().'>';
    }

    public function getDiscordTokenExpirationDate(): ?\DateTimeInterface
    {
        return $this->discordTokenExpirationDate;
    }

    public function setDiscordTokenExpirationDate(\DateTimeInterface $discordTokenExpirationDate): self
    {
        $this->discordTokenExpirationDate = $discordTokenExpirationDate;

        return $this;
    }
}
