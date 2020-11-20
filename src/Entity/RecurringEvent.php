<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecurringEventRepository")
 */
class RecurringEvent
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
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max=2000)
     */
    private ?string $description = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DiscordGuild", inversedBy="recurringEvents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private DiscordGuild $guild;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\Positive()
     */
    private int $createInAdvanceAmount = 1;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     * @Assert\DateTime
     */
    private ?\DateTimeInterface $date = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $timezone = 'UTC';

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $lastEventStartDate = null;

    /**
     * @ORM\Column(type="json")
     * @Assert\NotNull()
     * @Assert\Count(min=1)
     */
    private array $days = [];

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\Positive()
     */
    private int $weekInterval = 1;

    /**
     * @ORM\ManyToOne(targetEntity=DiscordChannel::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?DiscordChannel $reminderRerouteChannel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getGuild(): DiscordGuild
    {
        return $this->guild;
    }

    public function setGuild(DiscordGuild $guild): self
    {
        $this->guild = $guild;

        return $this;
    }

    public function getCreateInAdvanceAmount(): int
    {
        return $this->createInAdvanceAmount;
    }

    public function setCreateInAdvanceAmount(int $createInAdvanceAmount): self
    {
        $this->createInAdvanceAmount = $createInAdvanceAmount;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getLastEventStartDate(): \DateTimeInterface
    {
        return $this->lastEventStartDate;
    }

    public function setLastEventStartDate(\DateTimeInterface $lastEventStartDate): self
    {
        $this->lastEventStartDate = $lastEventStartDate;

        return $this;
    }

    public function getDays(): array
    {
        return $this->days;
    }

    public function setDays(array $days): self
    {
        $this->days = $days;

        return $this;
    }

    public function getWeekInterval(): int
    {
        return $this->weekInterval;
    }

    public function setWeekInterval(int $weekInterval): self
    {
        $this->weekInterval = $weekInterval;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name.' ('.$this->guild->getName().')';
    }

    public function getReminderRerouteChannel(): ?DiscordChannel
    {
        return $this->reminderRerouteChannel;
    }

    public function setReminderRerouteChannel(?DiscordChannel $reminderRerouteChannel): self
    {
        $this->reminderRerouteChannel = $reminderRerouteChannel;

        return $this;
    }
}
