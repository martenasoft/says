<?php

namespace App\Form;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\User;
use App\Service\RouteService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class PermissionType extends AbstractType
{
    public function __construct(private RouteService $routeService)
    {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $routes = $this->routeService->list('/^(app_|menu_)(.*)/');
        $routes = array_column($routes, 'name');
        array_unshift($routes, '');
        $routes = array_combine($routes, $routes);
        $builder
            ->add('name')
            ->add('route', ChoiceType::class, [
                'choices' => $routes
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permission::class,
        ]);
    }
}
