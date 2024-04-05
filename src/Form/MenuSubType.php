<?php

namespace App\Form;

use App\Entity\Interfaces\NodeInterface;
use App\Entity\Menu;
use App\Repository\MenuRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuSubType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('isBottomMenu')
            ->add('isLeftMenu')
            ->add('isTopMenu')
            ->add('type', ChoiceType::class, [
                'choices' => array_flip(Menu::TYPES)
            ])->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $formData = $event->getData();
                $form->add('parent', EntityType::class, [
                    'class' => Menu::class,
                    'empty_data' => '',
                    'required' => false,
                    'query_builder' => function (MenuRepository $menuRepository)  use ($formData)  {
                        $queryBuilder = $menuRepository->getAllMenuQueryBuilder();

                        if ($formData) {
                            $queryBuilder->andWhere('m.id!=:id')->setParameter('id', $formData->getId());
                        }
                        return $queryBuilder;
                    },
                    'choice_label' => function(NodeInterface $data) {
                        $pad = '';
                        for ($i = 1; $i < $data->getLvl(); $i++) {
                            $pad .= '-';
                        }
                        return (!empty($pad) ? '|' : ''). $pad.$data->getName() .' '.$data->getId();
                    }
                ]);
            })

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
