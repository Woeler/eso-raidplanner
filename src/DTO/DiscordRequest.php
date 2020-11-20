<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\DTO;

use App\Entity\DiscordGuild;
use App\Entity\User;

final class DiscordRequest
{
    private string $channelId;
    private DiscordGuild $guild;
    private User $user;
    private string $args = '';
    private ?string $command = null;

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): DiscordRequest
    {
        $this->channelId = $channelId;

        return $this;
    }

    public function getGuild(): DiscordGuild
    {
        return $this->guild;
    }

    public function setGuild(DiscordGuild $guild): DiscordRequest
    {
        $this->guild = $guild;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): DiscordRequest
    {
        $this->user = $user;

        return $this;
    }

    public function getArgs(): string
    {
        return $this->args;
    }

    public function setArgs(string $args): DiscordRequest
    {
        $this->args = trim($args);

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): DiscordRequest
    {
        $this->command = ltrim(trim($command), '!');

        return $this;
    }
}
