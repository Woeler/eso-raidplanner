<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Twig;

use App\Repository\PollOptionRepository;
use Doctrine\Common\Collections\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    private PollOptionRepository $pollOptionRepository;

    public function __construct(PollOptionRepository $pollOptionRepository)
    {
        $this->pollOptionRepository = $pollOptionRepository;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('poll_votes', [$this, 'pollVotes']),
        ];
    }

    public function pollVotes(int $pollOptionId): Collection
    {
        $option  = $this->pollOptionRepository->find($pollOptionId);

        return $option->getVotes();
    }
}
