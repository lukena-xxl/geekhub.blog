<?php

namespace App\Form\Users;

use App\Entity\Categories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('num', NumberType::class, [
                'required' => false,
                'label' => false,
                'html5' => true,
                'attr' => [
                    'placeholder' => 0,
                    'min' => 0,
                ],
                'empty_data' => '0',
            ])
            ->add('symbol', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'more' => 'more',
                    'less' => 'less',
                    'equally' => 'equally'
                ],
            ])
            ->add('category', EntityType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'all categories',
                'class' => Categories::class,
                'choice_label' => 'title',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Show',
                'attr' => [
                    'class' => 'btn-secondary btn-sm',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
