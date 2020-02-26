<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    private $hours = [
        0=>'12am',
        1=>'1am',
        2=>'2am',
        3=>'3am',
        4=>'4am',
        5=>'5am',
        6=>'6am',
        7=>'7am',
        8=>'8am',
        9=>'9am',
        10=>'10am',
        11=>'11am',
        12=>'12pm',
        13=>'1pm',
        14=>'2pm',
        15=>'3pm',
        16=>'4pm',
        17=>'5pm',
        18=>'6pm',
        19=>'7pm',
        20=>'8pm',
        21=>'9pm',
        22=>'10pm',
        23=>'11pm',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('start', DateTimeType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ],
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'view_timezone' => $options['timezone'],
            ])
            ->add('end', DateTimeType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ],
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'view_timezone' => $options['timezone'],
                'label' => 'Event end time (not required)',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary pull-right'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Event::class,
            'csrf_protection' => 'test' !== getenv('APP_ENV'),
            'timezone' => 'UTC',
            'clock' => 24,
        ]);
    }
}
