<?php

namespace App\Form;

use App\Entity\Interfaces\SeoInterface;
use App\Entity\Page;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Sodium\add;

class PageType extends AbstractType
{
    public function __construct(
        private ParameterBagInterface $parameter,
        private TranslatorInterface $translator
    )
    {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Name'
            ])
            ->add('lang', ChoiceType::class, [
                'choices' => array_flip($this->parameter->get('languages')),
                'label' => 'Lang'
            ])
            ->add('status', ChoiceType::class, [
                'choices' => array_flip(Page::STATUSES),
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_flip(Page::TYPES),
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
            ->add('menu', MenuSubType::class)
            ->add('image', FileType::class, [
                'label' => $this->translator->trans('Image') . " (".implode(' ', Page::MIME_TYPES).")",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => Page::MAX_SIZES,
                        'mimeTypes' => array_keys(Page::MIME_TYPES),
                        'mimeTypesMessage' => $this->translator->trans('Image') .
                                              ' ('.implode(', ', Page::MIME_TYPES).') ',
                    ])
                ],
            ])
            ->add('seoTitle', TextType::class, [
                'required' => false
            ])
            ->add('seoDescription', TextType::class, [
                'required' => false
            ])
            ->add('seoKeywords', TextType::class, [
                'required' => false
            ])

            ->add('ogTitle', TextType::class, [
                'required' => false
            ])
            ->add('ogDescription', TextType::class, [
                'required' => false
            ])
            ->add('ogUrl', TextType::class, [
                'required' => false
            ])
            ->add('ogImage', TextType::class, [
                'required' => false
            ])
            ->add('ogType', ChoiceType::class, [
                'choices' => array_flip(SeoInterface::OG_TYPES)
            ])

            ->add('isPreviewOnMain')
            ->add('seoKeywords', TextType::class, [
                'required' => false
            ])
            ->add('position', TextType::class, [
                'required' => false
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
