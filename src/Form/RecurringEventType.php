<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\RecurringEvent;
use App\Utility\TimezoneUtility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
            ->add('name', TextType::class, ['required' => true])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('createInAdvanceAmount', NumberType::class, ['required' => true])
            ->add('date', DateTimeType::class, ['required' => true])
            ->add(
                'timezone',
                ChoiceType::class,
                [
                    'required' => true,
                    'multiple' => false,
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
                        'Thursday' => 'TU',
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
        ]);
    }
}
