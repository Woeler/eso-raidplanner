<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\DiscordChannel;
use App\Entity\DiscordGuild;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscordGuildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'logChannel',
                EntityType::class,
                [
                    'class' => DiscordChannel::class,
                    'empty_data' => '',
                    'placeholder' => 'Discord channel for log messages',
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
            'data_class' => DiscordGuild::class,
            'guild' => null,
        ]);
    }
}
