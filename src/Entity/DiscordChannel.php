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
 * @ORM\Entity(repositoryClass="App\Repository\DiscordChannelRepository")
 */
class DiscordChannel
{
    public const CHANNEL_TYPE_TEXT = 0;

    public const CHANNEL_TYPE_VOICE = 2;

    public const CHANNEL_TYPE_CATEGORY = 4;

    public const ERROR_NONE = 0;

    public const ERROR_MISSING_PERMISSIONS = 1;

    public const ERROR_NOT_FOUND = 2;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     * @var string
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DiscordGuild", inversedBy="discordChannels")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var DiscordGuild
     */
    private $guild;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $error;

    public function __construct()
    {
        $this->error = 0;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return DiscordChannel
     */
    public function setId(string $id): DiscordChannel
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return DiscordGuild
     */
    public function getGuild(): DiscordGuild
    {
        return $this->guild;
    }

    /**
     * @param DiscordGuild $guild
     * @return DiscordChannel
     */
    public function setGuild(DiscordGuild $guild): DiscordChannel
    {
        $this->guild = $guild;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return DiscordChannel
     */
    public function setName(string $name): DiscordChannel
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return DiscordChannel
     */
    public function setType(int $type): DiscordChannel
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @param int $error
     * @return DiscordChannel
     */
    public function setError(int $error): DiscordChannel
    {
        $this->error = $error;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
