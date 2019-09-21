<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Repository\DiscordGuildRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(DiscordGuildRepository $discordGuildRepository, EntityManagerInterface $entityManager)
    {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->entityManager = $entityManager;
    }
}
