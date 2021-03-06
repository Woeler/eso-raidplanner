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
            new TwigFilter('poll_votes_percentage', [$this, 'pollVotesPercentage']),
        ];
    }

    public function pollVotes(int $pollOptionId): Collection
    {
        $option  = $this->pollOptionRepository->find($pollOptionId);

        return $option->getVotes();
    }

    public function pollVotesPercentage(int $pollOptionId): float
    {
        $option  = $this->pollOptionRepository->find($pollOptionId);

        return 0 === $option->getPoll()->getVotes()->count() ? 0 : round(($option->getVotes()->count() / $option->getPoll()->getVotes()->count()) * 100);
    }
}
