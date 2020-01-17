<?php

namespace App\Form\Users;

use App\Entity\Statuses;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Name',
                'attr' => [
                    'placeholder' => 'Enter a name',
                ],
            ])
            ->add('login', TextType::class, [
                'label' => 'Login',
                'attr' => [
                    'placeholder' => 'Enter a login',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'help' => 'Password must contain numbers and letters',
                'attr' => [
                    'placeholder' => 'Enter a password',
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Enter a email',
                ],
            ])
            ->add('status', EntityType::class, [
                'label' => 'Status',
                'class' => Statuses::class,
                'choice_label' => 'title',
            ])
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'label' => 'Gender',
                'choices' => [
                    'I\'m a man' => 'male',
                    'I am a woman' => 'female'
                ],
                'placeholder' => 'choose gender',
            ])
            ->add('birth_date', BirthdayType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Birth date',
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
