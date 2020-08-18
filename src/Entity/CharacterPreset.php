<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use App\Entity\Traits\HasEsoClass;
use App\Entity\Traits\HasEsoRole;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use App\Utility\HtmlUtility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Parsedown;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacterPresetRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CharacterPreset
{
    use HasEsoRole, HasEsoClass;

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
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\Positive()
     */
    private int $class = EsoClassUtility::CLASS_DRAGONKNIGHT;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\Positive()
     */
    private int $role = EsoRoleUtility::ROLE_TANK;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ArmorSet")
     */
    private Collection $sets;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="characterPresets")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private User $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max=5000)
     */
    private ?string $notes = null;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     * @Assert\NotNull()
     */
    private bool $notesPublic = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $notesHtml = null;

    public function __construct()
    {
        $this->sets = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

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

    public function getClass(): ?int
    {
        return $this->class;
    }

    public function setClass(int $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection|ArmorSet[]
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }

    public function addSet(ArmorSet $set): self
    {
        if (!$this->sets->contains($set)) {
            $this->sets[] = $set;
        }

        return $this;
    }

    public function removeSet(ArmorSet $set): self
    {
        if ($this->sets->contains($set)) {
            $this->sets->removeElement($set);
        }

        return $this;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getNotesPublic(): ?bool
    {
        return $this->notesPublic;
    }

    public function setNotesPublic(bool $notesPublic): self
    {
        $this->notesPublic = $notesPublic;

        return $this;
    }

    public function getNotesHtml(): ?string
    {
        return $this->notesHtml;
    }

    public function setNotesHtml(?string $notesHtml): self
    {
        $this->notesHtml = $notesHtml;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateHtmlNotes(): void
    {
        if (null !== $this->notes) {
            $parser = new Parsedown();
            $parser->setBreaksEnabled(true);
            $this->notesHtml = HtmlUtility::removeScriptTags($parser->parse($this->notes));
        } else {
            $this->notesHtml = null;
        }
    }
}
