<?php

namespace App\Form;

use App\Entity\Page;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices' => array_flip(Page::STATUSES),
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_flip(Page::TYPES),
            ])
            ->add('parent', EntityType::class, [
                'class' => Page::class,
                'choice_label' => 'name',
                'required' => false
            ])
            ->add('slug', TextType::class, [
                'required' => false
            ])
            ->add('preview', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
                'required' => false
            ])
            ->add('body', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
                'required' => false
            ])
            ->add('menuType', ChoiceType::class, [
                'choices' => array_flip(Page::MENU_TYPES),
            ])
            ->add('image', FileType::class, [
                'label' => "Image (".implode(' ', Page::MIME_TYPES).")",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => Page::MAX_SIZES,
                        'mimeTypes' => array_keys(Page::MIME_TYPES),
                        'mimeTypesMessage' => 'Please upload a valid ('.implode(', ', Page::MIME_TYPES).') image',
                    ])
                ],
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
