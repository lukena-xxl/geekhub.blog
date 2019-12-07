<?php

namespace App\Form\Users;

use App\Repository\StatusesRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    private $statusesRepository;

    public function __construct(StatusesRepository $statusesRepository)
    {
        $this->statusesRepository = $statusesRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statuses = $this->statusesRepository->findAll();
        $choices = [];
        foreach ($statuses as $status) {
            $choices[$status->getTitle()] = $status->getId();
        }

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
            ->add('status', ChoiceType::class, [
                'label' => 'Статус пользователя',
                'choices' => $choices,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Добавить пользователя',
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
