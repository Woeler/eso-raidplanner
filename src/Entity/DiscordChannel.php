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
     */
    private string $id;

    /**
     * @ORM\ManyToOne(targetEntity="DiscordGuild", inversedBy="discordChannels")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private DiscordGuild $guild;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer")
     */
    private int $type = self::CHANNEL_TYPE_TEXT;

    /**
     * @ORM\Column(type="integer")
     */
    private int $error = self::ERROR_NONE;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): DiscordChannel
    {
        $this->id = $id;

        return $this;
    }

    public function getGuild(): DiscordGuild
    {
        return $this->guild;
    }

    public function setGuild(DiscordGuild $guild): DiscordChannel
    {
        $this->guild = $guild;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DiscordChannel
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): DiscordChannel
    {
        $this->type = $type;

        return $this;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function setError(int $error): DiscordChannel
    {
        $this->error = $error;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
