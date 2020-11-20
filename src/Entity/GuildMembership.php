<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class GuildMembership
{
    public const ROLE_BANNED = 0;
    public const ROLE_MEMBER = 1;
    public const ROLE_ADMIN = 2;

    public const ROLES = [
        self::ROLE_BANNED => 'Banned',
        self::ROLE_MEMBER => 'Member',
        self::ROLE_ADMIN => 'Admin',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="guildMemberships", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity="DiscordGuild", inversedBy="members", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private DiscordGuild $guild;

    /**
     * @ORM\Column(type="integer")
     */
    private int $role = self::ROLE_MEMBER;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $nickname = null;

    /**
     * @ORM\Column(type="boolean", options={"default":1})
     */
    private bool $showOnCalendar = true;

    /**
     * @ORM\Column(type="string", length=255, options={"default":"3788d8"})
     * @Assert\Length(min=6,max=6)
     * @Assert\NotBlank
     * @Assert\NotNull
     */
    private string $colour = '3788d8';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

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

    public function getRole(): int
    {
        return $this->role;
    }

    public function getRoleString(): string
    {
        return self::ROLES[$this->role];
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function __toString(): string
    {
        return $this->user->getUsername().'#'.$this->user->getDiscordDiscriminator().' ('.$this->guild->getName().')';
    }

    public function getNickname(): string
    {
        return $this->nickname ?? $this->user->getUsername();
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getShowOnCalendar(): bool
    {
        return $this->showOnCalendar;
    }

    public function setShowOnCalendar(bool $showOnCalendar): self
    {
        $this->showOnCalendar = $showOnCalendar;

        return $this;
    }

    public function getColour(): string
    {
        return $this->colour;
    }

    public function setColour(string $colour): self
    {
        $this->colour = trim($colour, '#');

        return $this;
    }
}
