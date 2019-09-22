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
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;

class ReminderService
{
    /**
     * @var Event
     */
    private $event;

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getDiscordMessage(Reminder $notification): DiscordEmbedsMessage
    {
        $embeds = new DiscordEmbedsMessage();
        $embeds->setTitle('Reminder for '.$this->event->getName());
        $embeds->setUrl('');
        $embeds->setDescription($this->processText($notification->t));
        $embeds->setColor(9660137);
        $embeds->setAuthorName('ESO Raidplanner');
        $embeds->setAuthorIcon('https://esoraidplanner.com/favicon/appicon.jpg');
        $embeds->setAuthorUrl('https://esoraidplanner.com');
        $embeds->setFooterIcon('https://esoraidplanner.com/favicon/appicon.jpg');
        $embeds->setFooterText('ESO Raidplanner by Woeler');

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
