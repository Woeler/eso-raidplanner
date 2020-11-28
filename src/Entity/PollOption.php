<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use App\Repository\PollOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PollOptionRepository::class)
 */
class PollOption
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
    private string $value = '';

    /**
     * @ORM\ManyToOne(targetEntity=Poll::class, inversedBy="options")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Poll $poll;

    /**
     * @ORM\OneToMany(targetEntity=PollVote::class, mappedBy="pollOption", orphanRemoval=true)
     */
    private Collection $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function setPoll(?Poll $poll): self
    {
        $this->poll = $poll;

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
            $vote->setPollOption($this);
        }

        return $this;
    }

    public function removeVote(PollVote $vote): self
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getPollOption() === $this) {
                $vote->setPollOption(null);
            }
        }

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
