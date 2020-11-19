<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Message;

class DiscordMessage
{
    private string $channelId;
    private array $data;

    public function __construct(string $channelId, array $data)
    {
        $this->channelId = $channelId;
        $this->data = $data;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
