<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Entity\Traits;

use App\Utility\EsoRoleUtility;

trait HasEsoRole
{
    public function getRoleName(): string
    {
        return EsoRoleUtility::getRoleName($this->getRole());
    }

    public function getRoleIcon(): string
    {
        return EsoRoleUtility::getRoleIcon($this->getRole());
    }

    abstract public function getRole(): ?int;
}
