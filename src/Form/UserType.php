<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

class UserType extends AbstractType
{
    public function __construct(private RoleRepository $roleRepository)
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
                'choices' => array_flip(User::STATUSES),
            ])
            ->add('isVerified')
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
