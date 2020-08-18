<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Message\Discord\Bot;

interface BotCommandMessageInterface
{
    public function __construct(string $channelId, array $requestData);

    public function getRequestData(): array;

    public function getChannelId(): string;
}
