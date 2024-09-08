<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    public function __construct(
        private RoleRepository $roleRepository,
        private TranslatorInterface $translator
    )
    {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $userRoles = [];
        if ($options['data'] instanceof UserInterface) {
            $userRoles = $options['data']->getRoles();
        }

        $builder
            ->add('email')

            ->add('roles', ChoiceType::class, [
                'choices' => $this->getRoles(),
                'data' => $userRoles,
                'expanded' => true,
                'multiple' => true
            ])
            ->add('status', ChoiceType::class, [
                'choices' => array_flip(array_map(function($item) {
                    return $this->translator->trans($item);
                }, User::STATUSES)),
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Is Verified'
            ])
           ;


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
    private function getRoles(): array
    {
        $roles = $this->roleRepository->findAll();
        $rolesArray = User::ROLES;
        /**
         * @var Role $role
         */
        foreach ($roles as $role) {
            $rolesArray[] = $role->getName();
        }

        return array_combine($rolesArray, $rolesArray);
    }
}
