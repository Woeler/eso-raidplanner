<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @ORM\Entity()
 */
class Reminder
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * @ManyToOne(targetEntity="DiscordChannel")
     * @ORM\JoinColumn(name="discord_channel_id", referencedColumnName="id", nullable=true)
     * @var DiscordChannel
     */
    private $channel;

    /**
     * @ManyToOne(targetEntity="DiscordGuild", inversedBy="reminders")
     * @var DiscordGuild
     */
    private $guild;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $text;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $minutesToTrigger;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Reminder
     */
    public function setId(int $id): Reminder
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Reminder
     */
    public function setName(string $name): Reminder
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return DiscordChannel
     */
    public function getChannel(): ?DiscordChannel
    {
        return $this->channel;
    }

    /**
     * @param DiscordChannel $channel
     * @return Reminder
     */
    public function setChannel(DiscordChannel $channel): Reminder
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return DiscordGuild
     */
    public function getGuild(): ?DiscordGuild
    {
        return $this->guild;
    }

    /**
     * @param DiscordGuild $guild
     * @return Reminder
     */
    public function setGuild(DiscordGuild $guild): Reminder
    {
        $this->guild = $guild;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Reminder
     */
    public function setText(string $text): Reminder
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinutesToTrigger(): ?int
    {
        return $this->minutesToTrigger;
    }

    /**
     * @param int $minutesToTrigger
     * @return Reminder
     */
    public function setMinutesToTrigger(int $minutesToTrigger): Reminder
    {
        $this->minutesToTrigger = $minutesToTrigger;

        return $this;
    }
}
