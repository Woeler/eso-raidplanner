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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Reminder
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(min=1,max=200)
     */
    private string $name = '';

    /**
     * @ManyToOne(targetEntity="DiscordChannel")
     * @ORM\JoinColumn(name="discord_channel_id", referencedColumnName="id", nullable=true,onDelete="SET NULL")
     */
    private DiscordChannel $channel;

    /**
     * @ManyToOne(targetEntity="DiscordGuild", inversedBy="reminders")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private DiscordGuild $guild;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(max=2000)
     */
    private string $text = '';

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\Positive()
     */
    private int $minutesToTrigger = 60;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotNull()
     */
    private bool $detailedInfo = false;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotNull()
     */
    private bool $pingAttendees = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Reminder
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Reminder
    {
        $this->name = $name;

        return $this;
    }

    public function getChannel(): ?DiscordChannel
    {
        return $this->channel;
    }

    public function setChannel(DiscordChannel $channel): Reminder
    {
        $this->channel = $channel;

        return $this;
    }

    public function getGuild(): DiscordGuild
    {
        return $this->guild;
    }

    public function setGuild(DiscordGuild $guild): Reminder
    {
        $this->guild = $guild;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): Reminder
    {
        $this->text = $text;

        return $this;
    }

    public function getMinutesToTrigger(): int
    {
        return $this->minutesToTrigger;
    }

    public function setMinutesToTrigger(int $minutesToTrigger): Reminder
    {
        $this->minutesToTrigger = $minutesToTrigger;

        return $this;
    }

    public function isDetailedInfo(): bool
    {
        return $this->detailedInfo;
    }

    public function setDetailedInfo(bool $detailedInfo): Reminder
    {
        $this->detailedInfo = $detailedInfo;

        return $this;
    }

    public function isPingAttendees(): bool
    {
        return $this->pingAttendees;
    }

    public function setPingAttendees(bool $pingAttendees): Reminder
    {
        $this->pingAttendees = $pingAttendees;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name.' ('.$this->guild->getName().')';
    }
}
