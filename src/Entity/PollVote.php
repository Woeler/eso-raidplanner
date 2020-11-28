<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use App\Repository\PollVoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PollVoteRepository::class)
 */
class PollVote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Poll::class, inversedBy="votes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Poll $poll;

    /**
     * @ORM\ManyToOne(targetEntity=PollOption::class, inversedBy="votes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private PollOption $pollOption;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function setPoll(Poll $poll): self
    {
        $this->poll = $poll;

        return $this;
    }

    public function getPollOption(): PollOption
    {
        return $this->pollOption;
    }

    public function setPollOption(PollOption $pollOption): self
    {
        $this->pollOption = $pollOption;

        return $this;
    }
}
