<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Service;

use App\Entity\Event;
use App\Entity\Reminder;
use App\Utility\EsoRoleUtility;
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

    public function __construct(string $appUrl)
    {
        $this->appUrl = $appUrl;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getDiscordMessage(Reminder $notification): DiscordEmbedsMessage
    {
        $embeds = new DiscordEmbedsMessage();
        $embeds->setTitle('Reminder for '.$this->event->getName());
        $embeds->setUrl($this->appUrl.'/guild/'.$this->event->getGuild()->getId().'/event/'.$this->event->getId());
        $embeds->setDescription($this->processText($notification->getText()));
        $embeds->setColor(9660137);
        $embeds->setAuthorName($this->event->getGuild()->getName());
        $embeds->setAuthorIcon('https://cdn.discordapp.com/icons/'.$this->event->getGuild()->getId().'/'.$this->event->getGuild()->getIcon().'.png');
        $embeds->setAuthorUrl($this->appUrl.'/guild/'.$this->event->getGuild()->getId());
        $embeds->setFooterIcon('https://esoraidplanner.com/favicon/appicon.jpg');
        $embeds->setFooterText('ESO Raidplanner by Woeler');
        if ($notification->isPingAttendees()) {
            $mentions = [];
            foreach ($this->event->getAttendees() as $attendee) {
                $mentions[] = $attendee->getUser()->getDiscordMention();
            }
            $embeds->setContent(implode(',', $mentions));
        }
        if ($notification->isDetailedInfo()) {
            foreach (EsoRoleUtility::toArray() as $roleId => $roleName) {
                $attendees = $this->event->getAttendeesByRole($roleId);
                if (0 < count($attendees)) {
                    $text = '';
                    foreach ($attendees as $attendee) {
                        $text .= $attendee->getUser()->getDiscordMention().PHP_EOL;
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
