<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler\Discord\Bot;

use App\Message\Discord\Bot\EventsCommandMessage;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;

class EventsCommandHandler implements MessageHandlerInterface
{
    private DiscordGuildRepository $discordGuildRepository;

    private EventRepository $eventRepository;

    private UserRepository $userRepository;

    private DiscordBotService $discordBotService;

    private UrlGeneratorInterface $router;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        EventRepository $eventRepository,
        UserRepository $userRepository,
        DiscordBotService $discordBotService,
        UrlGeneratorInterface $router
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->discordBotService = $discordBotService;
        $this->router = $router;
    }

    public function __invoke(EventsCommandMessage $message): void
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $message->getRequestData()['guildId']]);
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);

        if (null === $user || null === $guild) {
            return;
        }

        $events = $this->eventRepository->findFutureEventsForGuild($guild);
        $desc = '';
        foreach ($events as $event) {
            $eventUrl = $this->router->generate(
                'guild_event_view',
                ['guildId' => $guild->getId(), 'eventId' => $event->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $eventName = '['.$event->getName().']('.$eventUrl.')';
            $desc .= $event->getId().': **'.$eventName.'**'.PHP_EOL.$user->toUserTimeString($event->getStart()).PHP_EOL.PHP_EOL;
        }

        $discordMessage = (new DiscordEmbedsMessage())
            ->setTitle('Upcoming events')
            ->setAuthorIcon('https://cdn.discordapp.com/icons/'.$guild->getId().'/'.$guild->getIcon().'.png')
            ->setAuthorName($guild->getName())
            ->addField('Times displayed in your timezone', $user->getTimezone())
            ->setDescription($desc);
        $discordMessage->setContent($user->getDiscordMention());

        $this->discordBotService->sendMessage($message->getChannelId(), $discordMessage);
    }
}
