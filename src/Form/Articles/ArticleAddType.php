<?php

namespace App\Form\Articles;

use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Tags;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArticleAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateTime = new \DateTime();

        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Enter a title',
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'label' => 'Slug',
                'help' => 'Characters allowed: [a-z] _ -',
                'attr' => [
                    'placeholder' => 'Enter a slug',
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Category',
                'class' => Categories::class,
                'choice_label' => 'title',
            ])
            ->add('tag', EntityType::class, [
                'required' => false,
                'label' => 'Tags',
                'class' => Tags::class,
                'choice_label' => 'title',
                'multiple' => true,
            ])
            ->add('user', EntityType::class, [
                'label' => 'Bind to user',
                'class' => Users::class,
                'choice_label' => 'login',
            ])
            ->add('body', TextareaType::class, [
                'required' => false,
                'label' => 'Publication',
                'attr' => [
                    'placeholder' => 'Write something',
                    'rows' => 10,
                    'class' => 'editor',
                ],
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'label' => 'Picture (JPG, JPEG)',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'You can upload jpg/jpeg images',
                    ])
                ],
                'attr' => [
                    'placeholder' => ' - not chosen - ',
                    'lang' => 'en',
                ],
            ])
            ->add('go_on_public', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Display schedule',
                'help' => 'Select a start date for the publication, or leave the field blank for immediate publication',
                'attr' => [
                    'min' => $dateTime->format('Y-m-d\TH:i'),
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
        $resolver->setDefaults([
            'data_class' => Articles::class,
        ]);
    }
}
