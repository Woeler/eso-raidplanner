<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\DiscordChannel;
use App\Entity\RecurringEvent;
use App\Utility\TimezoneUtility;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecurringEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'Event name',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => true,
                    'label' => 'Event description',
                ]
            )
            ->add(
                'createInAdvanceAmount',
                IntegerType::class,
                [
                    'required' => true,
                    'label' => 'Amount of events to be active at once (min: 1, max: 10)',
                    'attr' => ['min' => 1, 'max' => 10],
                ]
            )
            ->add(
                'date',
                DateTimeType::class,
                [
                    'required' => true,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'label' => 'Recurring event start date and time',
                ]
            )
            ->add(
                'timezone',
                ChoiceType::class,
                [
                    'required' => true,
                    'multiple' => false,
                    'data' => $options['timezone'],
                    'choices' => array_flip(TimezoneUtility::timeZones()),
                ]
            )
            ->add(
                'days',
                ChoiceType::class,
                [
                    'required' => true,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => [
                        'Monday' => 'MO',
                        'Tuesday' => 'TU',
                        'Wednesday' => 'WE',
                        'Thursday' => 'TH',
                        'Friday' => 'FR',
                        'Saturday' => 'SA',
                        'Sunday' => 'SU',
                    ],
                ]
            )
            ->add(
                'weekInterval',
                ChoiceType::class,
                [
                    'required' => true,
                    'multiple' => false,
                    'choices' => [
                        'Every week' => 1,
                        'Every two weeks' => 2,
                        'Every three weeks' => 3,
                        'Every four weeks' => 4,
                    ],
                ]
            )
            ->add(
                'reminderRerouteChannel',
                EntityType::class,
                [
                    'class' => DiscordChannel::class,
                    'empty_data' => '',
                    'label' => 'Re-route reminders to the following channel for these events',
                    'placeholder' => 'Use default channels',
                    'help' => 'If empty, guild default is used.',
                    'required' => false,
                    'query_builder' => static function (EntityRepository $er) use ($options) {
                        return $er->createQueryBuilder('u')
                            ->where('u.guild = :guild')
                            ->setParameter('guild', $options['guild']->getId())
                            ->orderBy('u.name', 'ASC');
                    },
                ]
            )
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary pull-right'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecurringEvent::class,
            'timezone' => 'UTC',
            'guild' => null,
        ]);
    }
}
