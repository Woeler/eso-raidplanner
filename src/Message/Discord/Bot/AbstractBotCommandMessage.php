<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Message\Discord\Bot;

abstract class AbstractBotCommandMessage implements BotCommandMessageInterface
{
    /**
     * @var string
     */
    private $channelId;

    /**
     * @var array
     */
    private $requestData;

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
