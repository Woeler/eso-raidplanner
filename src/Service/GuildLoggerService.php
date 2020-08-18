<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Service;

use App\Entity\DiscordChannel;
use App\Entity\DiscordGuild;
use App\Entity\Event;
use App\Entity\EventAttendee;
use App\Entity\GuildLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Woeler\DiscordPhp\Message\AbstractDiscordMessage;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;

class GuildLoggerService
{
    /**
     * @var string
     */
    private $appUrl;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(string $appUrl, EntityManagerInterface $entityManager, UrlGeneratorInterface $router)
    {
        $this->appUrl = $appUrl;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    private function persistLog(AbstractDiscordMessage $message, ?DiscordChannel $channel): void
    {
        if (null === $channel) {
            return;
        }

        if ($message instanceof DiscordEmbedsMessage) {
            $message->setColor(9660137);
            $message->setAuthorName($channel->getGuild()->getName());
            $message->setAuthorIcon('https://cdn.discordapp.com/icons/' . $channel->getGuild()->getId() . '/' . $channel->getGuild()->getIcon() . '.png');
            $message->setAuthorUrl($this->router->generate('guild_view', ['guildId' => $channel->getGuild()->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
            $message->setFooterIcon($this->appUrl.'/build/images/favicon/appicon.jpg');
            $message->setFooterText('ESO Raidplanner by Woeler');
        }

        $entity = (new GuildLog())
            ->setChannel($channel->getId())
            ->setData($message->formatForDiscord());
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function eventCreated(DiscordGuild $guild, Event $event): void
    {
        $message = new DiscordEmbedsMessage();
        $message->setTitle('Event created')
            ->setUrl($this->router->generate(
                'guild_event_view',
                ['guildId' => $guild->getId(), 'eventId' => $event->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ))
            ->setDescription('**'.$event->getName().'**'.PHP_EOL.$event->getDescription())
            ->addField('Event ID', (string)$event->getId());

        $this->persistLog($message, $guild->getLogChannel());
        $this->persistLog($message, $guild->getEventCreateChannel());
    }

    public function eventUpdated(DiscordGuild $guild, Event $event): void
    {
        $message = new DiscordEmbedsMessage();
        $message->setTitle('Event updated')
            ->setUrl($this->router->generate(
                'guild_event_view',
                ['guildId' => $guild->getId(), 'eventId' => $event->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ))
            ->setDescription('**'.$event->getName().'**'.PHP_EOL.$event->getDescription());

        $this->persistLog($message, $guild->getLogChannel());
    }

    public function eventDeleted(DiscordGuild $guild, Event $event): void
    {
        $message = new DiscordEmbedsMessage();
        $message->setTitle('Event deleted')
            ->setDescription('**'.$event->getName().'**'.PHP_EOL.$event->getDescription());

        $this->persistLog($message, $guild->getLogChannel());
    }

    public function eventAttending(DiscordGuild $guild, Event $event, EventAttendee $attendee): void
    {
        $message = new DiscordEmbedsMessage();
        $message->setTitle('User is attending event')
            ->addField(
                'Event',
                '['.$event->getName().']('.
                $this->router->generate(
                    'guild_event_view',
                    ['guildId' => $guild->getId(), 'eventId' => $event->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ).')',
                true
            )
            ->addField('User', $attendee->getUser()->getDiscordMention(), true);

        $this->persistLog($message, $guild->getLogChannel());
    }

    public function eventUnattending(DiscordGuild $guild, Event $event, EventAttendee $attendee): void
    {
        $message = new DiscordEmbedsMessage();
        $message->setTitle('User is no longer attending event')
            ->addField(
                'Event',
                '['.$event->getName().']('.
                $this->router->generate(
                    'guild_event_view',
                    ['guildId' => $guild->getId(), 'eventId' => $event->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ).')',
                true
            )
            ->addField('User', $attendee->getUser()->getDiscordMention());

        $this->persistLog($message, $guild->getLogChannel());
    }
}
