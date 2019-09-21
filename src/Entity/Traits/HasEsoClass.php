<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity\Traits;

use App\Utility\EsoClassUtility;

trait HasEsoClass
{
    public function getClassName(): string
    {
        return EsoClassUtility::getClassName($this->getClass());
    }

    public function getClasIcon(): string
    {
        return EsoClassUtility::getClassIcon($this->getClass());
    }

    abstract public function getClass(): ?int;
}
