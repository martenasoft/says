<?php

namespace App\EventSubscriber;

use App\Entity\Permission;
use App\Entity\User;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class RoutePermissionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private PermissionRepository $permissionRepository,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router
    ) {

    }
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $user = $this->security->getUser();

        if (!$route || ($user && $user->hasRoles([User::ADMIN_ROLE]))) {
            return;
        }

        $permission = $this->permissionRepository->findOneByRoute($route);

        if (!$permission) {
            return;
        }

        if (
            (empty($permission->getCountOfUsers()) && empty($permission->getCountOfRoles())) ||
            (!empty($route) && !$this->permissionRepository->hasUserRoute($user, $route))
        ) {
            throw new AccessDeniedHttpException('You have no access to this url');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
