<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\CharacterPreset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CharacterPreset|null find($id, $lockMode = null, $lockVersion = null)
 * @method CharacterPreset|null findOneBy(array $criteria, array $orderBy = null)
 * @method CharacterPreset[]    findAll()
 * @method CharacterPreset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CharacterPresetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterPreset::class);
    }
}
