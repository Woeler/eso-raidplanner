<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Service;

use App\Entity\Event;
use App\Entity\Reminder;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;

class ReminderService
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $appUrl;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(string $appUrl, UrlGeneratorInterface $router)
    {
        $this->appUrl = $appUrl;
        $this->router = $router;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getDiscordMessage(Reminder $notification): DiscordEmbedsMessage
    {
        $embeds = new DiscordEmbedsMessage();
        $embeds->setTitle('Reminder for '.$this->event->getName().' (ID: '.$this->event->getId().')');
        $embeds->setUrl($this->appUrl.'/guild/'.$this->event->getGuild()->getId().'/event/'.$this->event->getId().'/view');
        $embeds->setDescription($this->processText($notification->getText()));
        $embeds->setColor(9660137);
        $embeds->setAuthorName($this->event->getGuild()->getName());
        $embeds->setAuthorIcon('https://cdn.discordapp.com/icons/'.$this->event->getGuild()->getId().'/'.$this->event->getGuild()->getIcon().'.png');
        $embeds->setAuthorUrl($this->router->generate('guild_view', ['guildId' => $this->event->getGuild()->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
        $embeds->setFooterIcon($this->appUrl.'/build/images/favicon/appicon.jpg');
        $embeds->setFooterText('Reminder: '.$notification->getName());
        if ($notification->isPingAttendees()) {
            $mentions = [];
            foreach ($this->event->getAttendees() as $attendee) {
                $mentions[] = $attendee->getStatusEmoji().' '.$attendee->getUser()->getDiscordMention();
            }
            $embeds->setContent(implode(',', $mentions));
        }
        if ($notification->isDetailedInfo()) {
            foreach (EsoRoleUtility::toArray() as $roleId => $roleName) {
                $attendees = $this->event->getAttendeesByRole($roleId);
                if (0 < count($attendees)) {
                    $text = '';
                    foreach ($attendees as $attendee) {
                        $text .= trim($attendee->getStatusEmoji().' '
                            .$attendee->getUser()->getDiscordMention().' '
                            .EsoClassUtility::getClassDiscordEmoji($attendee->getClass())).PHP_EOL;
                    }
                    $embeds->addField($roleName, $text, true);
                }
            }
        }

        return $embeds;
    }

    private function processText(?string $text): string
    {
        if (null === $text) {
            return '';
        }
        if (!empty($this->event)) {
            $text = str_replace(['{{EVENT_NAME}}', '{{EVENT_DESCRIPTION}}'], [$this->event->getName(), $this->event->getDescription()], $text);
        }

        return $text;
    }
}
