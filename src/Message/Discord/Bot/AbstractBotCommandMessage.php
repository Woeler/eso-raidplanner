<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Message\Discord\Bot;

abstract class AbstractBotCommandMessage implements BotCommandMessageInterface
{
    private string $channelId;

    private array $requestData;

    public function __construct(string $channelId, array $requestData)
    {
        $this->channelId = $channelId;
        $this->requestData = $requestData;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }
}
