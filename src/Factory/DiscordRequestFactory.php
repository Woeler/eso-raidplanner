<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Factory;

use App\DTO\DiscordRequest;
use App\Repository\DiscordGuildRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

final class DiscordRequestFactory
{
    public const GUILD_NOT_FOUND = 1605645415;
    public const USER_NOT_FOUND = 1605645467;
    public const CHANNEL_NOT_FOUND = 1605645776;

    private DiscordGuildRepository $guildRepository;
    private UserRepository $userRepository;

    public function __construct(DiscordGuildRepository $guildRepository, UserRepository $userRepository)
    {
        $this->guildRepository = $guildRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return DiscordRequest
     * @throws \JsonException
     * @throws \InvalidArgumentException
     */
    public function fromRequest(Request $request): DiscordRequest
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['channelId'])) {
            throw new \InvalidArgumentException('Channel not found', self::CHANNEL_NOT_FOUND);
        }
        $guild = $this->guildRepository->find($data['guildId']);
        if (null === $guild || !$guild->isActive()) {
            throw new \InvalidArgumentException('Guild not found', self::GUILD_NOT_FOUND);
        }
        $user = $this->userRepository->findOneBy(['discordId' => $data['userId']]);
        if (null === $user) {
            throw new \InvalidArgumentException('User not found', self::USER_NOT_FOUND);
        }

        $commandData = explode(' ', $data['args'] ?? '', 2);

        return (new DiscordRequest())
            ->setGuild($guild)
            ->setUser($user)
            ->setChannelId($data['channelId'])
            ->setCommand($commandData[0] ? mb_strtolower($commandData[0]) : null)
            ->setArgs($commandData[1] ?? '');
    }
}
