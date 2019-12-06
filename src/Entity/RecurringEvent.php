<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DiscordGuild", inversedBy="recurringEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $guild;

    /**
     * @ORM\Column(type="integer")
     */
    private $createInAdvanceAmount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $timezone;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastEventStartDate;

    /**
     * @ORM\Column(type="json")
     */
    private $days = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $weekInterval = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
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

    public function getGuild(): ?DiscordGuild
    {
        return $this->guild;
    }

    public function setGuild(?DiscordGuild $guild): self
    {
        $this->guild = $guild;

        return $this;
    }

    public function getCreateInAdvanceAmount(): ?int
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

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getLastEventStartDate(): ?\DateTime
    {
        return $this->lastEventStartDate;
    }

    public function setLastEventStartDate(\DateTime $lastEventStartDate): self
    {
        $this->lastEventStartDate = $lastEventStartDate;

        return $this;
    }

    public function getDays(): ?array
    {
        return $this->days;
    }

    public function setDays(array $days): self
    {
        $this->days = $days;

        return $this;
    }

    public function getWeekInterval(): ?int
    {
        return $this->weekInterval;
    }

    public function setWeekInterval(int $weekInterval): self
    {
        $this->weekInterval = $weekInterval;

        return $this;
    }
}
