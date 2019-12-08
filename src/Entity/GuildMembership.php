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
}
