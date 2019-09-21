<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\ArmorSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ArmorSet|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArmorSet|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArmorSet[]    findAll()
 * @method ArmorSet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArmorSetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ArmorSet::class);
    }

    public function searchByName(string $query)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name LIKE :word')
            ->setParameter('word', '%'.addcslashes($query, '%_').'%')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
