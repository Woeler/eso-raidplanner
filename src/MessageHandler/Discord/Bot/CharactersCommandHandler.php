<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler\Discord\Bot;

use App\Message\Discord\Bot\CharactersCommandMessage;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;
use Woeler\DiscordPhp\Message\DiscordTextMessage;

class CharactersCommandHandler implements MessageHandlerInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    public function __construct(
        UserRepository $userRepository,
        DiscordBotService $discordBotService
    ) {
        $this->userRepository = $userRepository;
        $this->discordBotService = $discordBotService;
    }

    public function __invoke(CharactersCommandMessage $message)
    {
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);
        $presets = $user->getCharacterPresets();

        if (0 === count($presets)) {
            $discordMessage = new DiscordTextMessage();
            $discordMessage->setContent($user->getDiscordMention().' You currently have no character presets.');
        } else {
            $discordMessage = new DiscordEmbedsMessage();
            foreach ($presets as $preset) {
                $discordMessage->addField(
                    $preset->getName(),
                    $preset->getClassName() . ' ' . $preset->getRoleName() . ' ' . (0 < $preset->getSets()->count() ? 'with sets ' . implode(', ', $preset->getSets()->toArray()) : ''),
                    false
                );
            }
        }

        $this->discordBotService->sendMessage($message->getChannelId(), $discordMessage);
    }
}
