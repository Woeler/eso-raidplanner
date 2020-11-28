<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use App\Repository\PollRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PollRepository::class)
 */
class Poll
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=1,max=200)
     */
    private string $question = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $multipleChoice = false;

    /**
     * @ORM\OneToMany(targetEntity=PollVote::class, mappedBy="poll", orphanRemoval=true)
     */
    private Collection $votes;

    /**
     * @ORM\OneToMany(targetEntity=PollOption::class, mappedBy="poll", orphanRemoval=true, cascade={"persist","remove"})
     * @ORM\OrderBy({"value" = "ASC"})
     */
    private Collection $options;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Event", cascade={"persist"}, mappedBy="poll")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Event $event;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function isMultipleChoice(): bool
    {
        return $this->multipleChoice;
    }

    public function setMultipleChoice(bool $multipleChoice): self
    {
        $this->multipleChoice = $multipleChoice;

        return $this;
    }

    /**
     * @return Collection|PollVote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(PollVote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setPoll($this);
        }

        return $this;
    }

    public function removeVote(PollVote $vote): self
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getPoll() === $this) {
                $vote->setPoll(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PollOption[]
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(PollOption $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setPoll($this);
        }

        return $this;
    }

    public function removeOption(PollOption $option): self
    {
        if ($this->options->removeElement($option)) {
            // set the owning side to null (unless already changed)
            if ($option->getPoll() === $this) {
                $option->setPoll(null);
            }
        }

        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }
}
