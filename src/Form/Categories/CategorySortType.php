<?php

namespace App\Form\Categories;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorySortType extends AbstractType
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
