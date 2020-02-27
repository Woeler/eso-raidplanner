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
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="guildMemberships", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var User
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="DiscordGuild", inversedBy="members", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var DiscordGuild
     */
    private $guild;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nickname;

    /**
     * @ORM\Column(type="boolean", options={"default":1})
     */
    private $showOnCalendar = true;

    /**
     * @ORM\Column(type="string", length=255, options={"default":"3788d8"})
     * @Assert\Length(min=6,max=6)
     * @Assert\NotBlank
     * @Assert\NotNull
     */
    private $colour;

    public function __construct()
    {
        $this->role = self::ROLE_MEMBER;
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
     * @return GuildMembership
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return GuildMembership
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGuild()
    {
        return $this->guild;
    }

    /**
     * @param mixed $guild
     * @return GuildMembership
     */
    public function setGuild($guild)
    {
        $this->guild = $guild;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    public function getRoleString(): string
    {
        return self::ROLES[$this->role];
    }

    /**
     * @param mixed $role
     * @return GuildMembership
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    public function __toString()
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

    public function getColour(): ?string
    {
        return $this->colour;
    }

    public function setColour(string $colour): self
    {
        $this->colour = trim($colour, '#');

        return $this;
    }
}
