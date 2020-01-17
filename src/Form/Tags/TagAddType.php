<?php

namespace App\Form\Tags;

use App\Entity\Tags;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Enter a title',
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'label' => 'Slug тега',
                'help' => 'Characters allowed: [a-z] _ -',
                'attr' => [
                    'placeholder' => 'Enter a slug',
                ],
            ])
            ->add('is_visible', CheckboxType::class, [
                'required' => false,
                'value' => 1,
                'label' => 'Enabled / Disabled',
                'label_attr' => [
                    'class' => 'font-weight-normal text-dark',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
