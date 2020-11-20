<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Utility\TimezoneUtility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class User extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'clock',
                ChoiceType::class,
                [
                    'choices' => array_flip([
                        12 => '12 hour format (am/pm)',
                        24 => '24 hour format',
                    ]),
                    'required' => true,
                ]
            )
            ->add(
                'timezone',
                ChoiceType::class,
                [
                    'choices' => array_flip(TimezoneUtility::timeZones()),
                    'required' => true,
                ]
            )
            ->add(
                'firstDayOfWeek',
                ChoiceType::class,
                [
                    'choices' => array_flip([
                        0 => 'Sunday',
                        1 => 'Monday',
                    ]),
                    'required' => true,
                ]
            )
            ->add(
                'darkmode',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => array_flip([0 => 'Light theme', 1 => 'Dark theme']),
                    'label' => 'Theme',
                ]
            )
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary pull-right'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\User::class,
            'csrf_protection' => 'test' !== getenv('APP_ENV'),
        ]);
    }
}
