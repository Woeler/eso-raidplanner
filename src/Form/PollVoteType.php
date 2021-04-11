<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\Poll;
use App\Entity\PollOption;
use App\Entity\PollVote;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollVoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Poll $poll */
        $poll = $options['poll'];
        $builder
            ->add('pollOption', EntityType::class, [
                'label' => $poll->getQuestion(),
                'required' => true,
                'class' => PollOption::class,
                'choice_label' => 'value',
                'choices' => $poll->getOptions(),
                'multiple' => $poll->isMultipleChoice(),
                'expanded' => true,
                'disabled' => new \DateTime() > $poll->getEvent()->getStart() || !$poll->getEvent()->isAttending($options['user'] ?? new \App\Entity\User()),
                'data' => $poll->isMultipleChoice() ? array_map(static function (PollVote $v) {
                    return $v->getPollOption();
                }, $options['votes']) : (isset($options['votes'][0]) ? $options['votes'][0]->getPollOption() : null),
            ]);
        if (new \DateTime() < $poll->getEvent()->getStart()) {
            $builder->add('submit', SubmitType::class, [
                'label' => 'Vote',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'poll' => null,
            'votes' => [],
            'user' => null
        ]);
    }
}
