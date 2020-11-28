<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\Poll;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'question',
                TextType::class,
                [
                    'label' => 'Poll question',
                    'required' => true,
                ]
            );
        if (null === $builder->getData()->getId()) {
            $builder->add(
                'multipleChoice',
                CheckboxType::class,
                [
                    'label' => 'Allow people to pick multiple answers (this cannot be changed without deleting the poll!)',
                    'value' => true,
                    'required' => false,
                ]
            );
        }
        $builder->add(
            'options',
            CollectionType::class,
            [
                'entry_type' => PollOptionType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => false,
            ]
        )
        ->add('submit', SubmitType::class, [
            'label' => 'Save',
            'attr' => ['class' => 'btn btn-primary pull-right'],
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Poll::class,
        ]);
    }
}
