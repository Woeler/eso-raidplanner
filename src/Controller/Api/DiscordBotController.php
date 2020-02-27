<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller\Api;

use App\Controller\Checks\TalksWithDiscordBotController;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Message\Discord\Bot\AttendCommandMessage;
use App\Message\Discord\Bot\EventCommandMessage;
use App\Message\Discord\Bot\EventsCommandMessage;
use App\Repository\CharacterPresetRepository;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventAttendeeRepository;
use App\Repository\EventRepository;
use App\Repository\GuildMembershipRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use App\Service\GuildLoggerService;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use App\Utility\TimezoneUtility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Woeler\DiscordPhp\Message\AbstractDiscordMessage;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;
use Woeler\DiscordPhp\Message\DiscordTextMessage;

/**
 * @Route("/api/discord", name="api_discord_")
 */
class DiscordBotController extends AbstractController implements TalksWithDiscordBotController
{
    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventAttendeeRepository
     */
    private $eventAttendeeRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GuildLoggerService
     */
    private $guildLoggerService;

    /**
     * @var CharacterPresetRepository
     */
    private $characterPresetRepository;

    /**
     * @var GuildMembershipRepository
     */
    private $guildMembershipRepository;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(
        DiscordBotService $discordBotService,
        DiscordGuildRepository $discordGuildRepository,
        EventRepository $eventRepository,
        EventAttendeeRepository $eventAttendeeRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        GuildLoggerService $guildLoggerService,
        CharacterPresetRepository $characterPresetRepository,
        GuildMembershipRepository $guildMembershipRepository,
        MessageBusInterface $bus
    ) {
        $this->discordBotService = $discordBotService;
        $this->discordGuildRepository = $discordGuildRepository;
        $this->eventRepository = $eventRepository;
        $this->eventAttendeeRepository = $eventAttendeeRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->guildLoggerService = $guildLoggerService;
        $this->characterPresetRepository = $characterPresetRepository;
        $this->guildMembershipRepository = $guildMembershipRepository;
        $this->bus = $bus;
    }

    /**
     * @Route("/bot", name="bot_entry_point", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function entryPoint(Request $request): Response
    {
        $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        try {
            switch ($json['command'] ?? null) {
                case '!events':
                    $this->bus->dispatch(new EventsCommandMessage($json['channelId'], $json));
                    break;
                case '!event':
                    $this->bus->dispatch(new EventCommandMessage($json['channelId'], $json));
                    break;
                case '!attend':
                    $this->bus->dispatch(new AttendCommandMessage($json['channelId'], $json));
                    break;
                case '!unattend':
                    $this->unattend($json);
                    break;
                case '!help':
                    $this->help($json);
                    break;
                case '!timezone':
                    $this->timeZone($json);
                    break;
                case '!characters':
                    $this->characters($json);
                    break;
                default:
                    return Response::create('', Response::HTTP_NO_CONTENT);
            }
        } catch (UnexpectedDiscordApiResponseException $e) {
            if (!empty($json['channelId'])) {
                $this->replyWithText('Oops, something went wrong.', $json['channelId']);
            }

            return Response::create('', Response::HTTP_BAD_REQUEST);
        }

        return Response::create('ok', Response::HTTP_OK);
    }

    /**
     * @param array $data
     * @throws UnexpectedDiscordApiResponseException
     */
    public function unattend(array $data): void
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $data['guildId']]);
        $event = $this->eventRepository->find(trim($data['query']));
        $user = $this->userRepository->findOneBy(['discordId' => $data['userId']]);
        if (null === $event || $event->getGuild()->getId() !== $guild->getId()) {
            $this->replyWithText('I don\'t know that event.', $data['channelId']);

            return;
        }

        $attendee = $this->eventAttendeeRepository->findOneBy(['user' => $user, 'event' => $event]);
        if (null !== $attendee) {
            $this->guildLoggerService->eventUnattending($guild, $event, $attendee);
            $this->entityManager->remove($attendee);
            $this->entityManager->flush();
        }

        $this->replyWithText(
            $user->getDiscordMention().' you are no longer attending '.$event->getName(),
            $data['channelId']
        );
    }

    /**
     * @param array $data
     * @throws UnexpectedDiscordApiResponseException
     */
    public function help(array $data): void
    {
        $user = $this->userRepository->findOneBy(['discordId' => $data['userId']]);
        $message = (new DiscordEmbedsMessage())
            ->addField('List all events', '!events')
            ->addField('Show specific event', '!event [eventID]'.PHP_EOL.'**Example**: `!event 1`')
            ->addField('Attend event', '!attend [eventId] [class] [role]'.PHP_EOL.'**Example**: `!attend 1 dragonknight tank`')
            ->addField('Leave event', '!unattend [eventId]'.PHP_EOL.'**Example**: `!unattend 1`')
            ->addField('See your character presets', '`!characters`')
            ->addField('Usable classes', implode(', ', EsoClassUtility::toArray()))
            ->addField('Usable roles', implode(', ', EsoRoleUtility::toArray()));
        $message->setContent($user->getDiscordMention());

        $this->replyWith($message, $data['channelId']);
    }

    /**
     * @param array $data
     * @throws UnexpectedDiscordApiResponseException
     */
    public function timeZone(array $data): void
    {
        $timezone = trim($data['query']);
        $user = $this->userRepository->findOneBy(['discordId' => $data['userId']]);

        if (array_key_exists($timezone, TimezoneUtility::timeZones())) {
            $user->setTimezone($timezone);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->replyWithText($user->getDiscordMention().' Your timezone has been set to '.$timezone.'.', $data['channelId']);

            return;
        }
        if (empty($timezone)) {
            $this->replyWithText(
                $user->getDiscordMention().' Please specify a timezone in your command. Here is an example `!timezone Europe/Berlin`, and here is a list of supported timezones https://www.php.net/manual/en/timezones.php',
                $data['channelId']
            );

            return;
        }
        $this->replyWithText(
            $user->getDiscordMention().' I do not know the timezone '.$timezone.'. Please make sure to check out this official timezone list. Also be aware that the timezone you give me is case sensitive. https://www.php.net/manual/en/timezones.php'
            .PHP_EOL.'Here is an example `!timezone Europe/Berlin`',
            $data['channelId']
        );
    }

    /**
     * @param array $data
     * @throws UnexpectedDiscordApiResponseException
     */
    public function characters(array $data): void
    {
        $user = $this->userRepository->findOneBy(['discordId' => $data['userId']]);
        $presets = $user->getCharacterPresets();

        if (0 === count($presets)) {
            $this->replyWithText($user->getDiscordMention().' You currently have no character presets.', $data['channelId']);

            return;
        }

        $message = new DiscordEmbedsMessage();
        foreach ($presets as $preset) {
            $message->addField(
                $preset->getName(),
                $preset->getClassName().' '.$preset->getRoleName().' '.(0 < $preset->getSets()->count() ? 'with sets '.implode(', ', $preset->getSets()->toArray()) : ''),
                false
            );
        }

        $this->replyWith($message, $data['channelId']);
    }

    /**
     * @param AbstractDiscordMessage $message
     * @param string $chanelId
     * @throws UnexpectedDiscordApiResponseException
     */
    protected function replyWith(AbstractDiscordMessage $message, string $chanelId): void
    {
        if ($message instanceof DiscordEmbedsMessage) {
            $message->setFooterIcon('https://esoraidplanner.com/build/images/favicon/appicon.jpg');
            $message->setFooterText('ESO Raidplanner by Woeler');
            $message->setColor(9660137);
        }

        $this->discordBotService->sendMessage($chanelId, $message);
    }

    /**
     * @param string $text
     * @param string $channelId
     * @throws UnexpectedDiscordApiResponseException
     */
    protected function replyWithText(string $text, string $channelId): void
    {
        $message = new DiscordTextMessage();
        $message->setContent($text);

        $this->discordBotService->sendMessage($channelId, $message);
    }
}
