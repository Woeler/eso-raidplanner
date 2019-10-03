<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\DiscordChannel;
use App\Entity\Reminder;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReminderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('text', TextareaType::class, ['required' => true])
            ->add('minutesToTrigger', IntegerType::class, ['required' => true])
            ->add(
                'channel',
                EntityType::class,
                [
                        'class' => DiscordChannel::class,
                        'query_builder' => function (EntityRepository $er) use ($options) {
                            return $er->createQueryBuilder('u')
                                ->where('u.guild = :guild')
                                ->setParameter('guild', $options['guild']->getId())
                                ->orderBy('u.name', 'ASC');
                        },
                    ]
            )
            ->add(
                'detailedInfo',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => ['Yes' => 1, 'No' => 0],
                    'label' => 'Include attendee list in message',
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
            'data_class' => Reminder::class,
            'guild' => null,
        ]);
    }
}
