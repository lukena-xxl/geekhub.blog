<?php

namespace App\Form\Users;

use App\Entity\Statuses;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'label' => 'Имя',
                'attr' => [
                    'placeholder' => 'Введите имя',
                ],
            ])
            ->add('login', TextType::class, [
                'label' => 'Логин',
                'attr' => [
                    'placeholder' => 'Введите логин',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Пароль',
                'help' => 'Пароль должен содержать цифры и буквы',
                'attr' => [
                    'placeholder' => 'Введите пароль',
                ],
            ])
            ->add('status', EntityType::class, [
                'label' => 'Статус пользователя',
                'class' => Statuses::class,
                'choice_label' => 'title',
            ])
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'label' => 'Пол',
                'choices' => [
                    'я мужчина' => 'male',
                    'я женщина' => 'female'
                ],
                'placeholder' => 'выберите пол',
            ])
            ->add('birth_date', BirthdayType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Дата рождения',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Сохранить',
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
