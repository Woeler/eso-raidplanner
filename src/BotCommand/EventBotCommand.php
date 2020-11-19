<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\BotCommand;

use App\DTO\DiscordRequest;
use App\DTO\DiscordResponse;
use App\Repository\EventRepository;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventBotCommand implements BotCommandInterface
{
    private EventRepository $eventRepository;
    private UrlGeneratorInterface $router;

    public function __construct(EventRepository $eventRepository, UrlGeneratorInterface $router)
    {
        $this->eventRepository = $eventRepository;
        $this->router = $router;
    }

    public function handle(DiscordRequest $request): DiscordResponse
    {
        $response = new DiscordResponse();

        if (empty(trim($request->getArgs()))) {
            $event = $this->eventRepository->findFirstFutureEvent($request->getGuild());
        } else {
            $event = $this->eventRepository->find(trim($request->getArgs()));
        }

        if (null === $event || $event->getGuild()->getId() !== $request->getGuild()->getId()) {
            $response
                ->setContent($request->getUser()->getDiscordMention().' I don\'t know that event.')
                ->setOnlyText(true);
        } else {
            $response
                ->setTitle('[' . $event->getId() . '] ' . $event->getName())
                ->setUrl($this->router->generate(
                    'guild_event_view',
                    ['guildId' => $request->getGuild()->getId(), 'eventId' => $event->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ))
                ->setAuthorIcon('https://cdn.discordapp.com/icons/' . $request->getGuild()->getId() . '/' . $request->getGuild()->getIcon() . '.png')
                ->setAuthorName($request->getGuild()->getName())
                ->setDescription($event->getDescription() ?? '')
                ->addField(
                    'Date and Time',
                    $request->getUser()->toUserTimeString($event->getStart()) . PHP_EOL . '(in your timezone: ' . $request->getUser()->getTimezone() . ')'
                )
                ->setContent($request->getUser()->getDiscordMention());

            foreach (EsoRoleUtility::toArray() as $roleId => $roleName) {
                $attendees = $event->getAttendeesByRole($roleId);
                if (0 < count($attendees)) {
                    $text = '';
                    foreach ($attendees as $attendee) {
                        $text .= trim($attendee->getStatusEmoji() . ' '
                                . $attendee->getUser()->getDiscordMention() . ' '
                                . EsoClassUtility::getClassDiscordEmoji($attendee->getClass())) . PHP_EOL;
                    }
                    $response->addField($roleName, $text, true);
                }
            }
        }

        return $response;
    }
}
