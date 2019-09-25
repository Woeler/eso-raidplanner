<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

///**
// * @ORM\Entity()
// */
//class CharacterPreset
//{
//    /**
//     * @ORM\Id()
//     * @ORM\GeneratedValue()
//     * @ORM\Column(type="integer")
//     * @var int
//     */
//    private $id;
//
//    /**
//     * @ORM\Column(type="string", length=255)
//     * @var string
//     */
//    private $name;
//
//    /**
//     * @ORM\Column(type="integer")
//     * @var int
//     */
//    private $class;
//
//    /**
//     * @ORM\Column(type="integer")
//     * @var int
//     */
//    private $role;
//
//    /**
//     * @ORM\ManyToMany(targetEntity="ArmorSet")
//     * @var Collection|ArmorSet[]
//     */
//    private $sets;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="User", inversedBy="characterPresets")
//     * @var User
//     */
//    private $user;
//}
