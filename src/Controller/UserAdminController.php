<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\UserRoleService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class UserAdminController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user_admin_index', methods: ['GET'])]
    public function index(
        PaginatorInterface $paginator,
        Request            $request,
        UserRepository $userRepository
    ): Response
    {
        $queryBuilder = $userRepository->getAllQueryBuilder();
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1));

        return $this->render('user_admin/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/admin/user/new', name: 'app_user_admin_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRoleService $roleService
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleService->addUserRoles($user, $user->getRoles());
            $user->setPassword('');
            $user->setStatus(User::STATUS_BLOCKED);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_profile_change_password', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_admin/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/admin/user/edit/{id}', name: 'app_user_admin_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserRoleService $roleService
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $roleService->addUserRoles($user, $user->getRoles());
            if (empty($user->getPassword())) {
                $user->setStatus(User::STATUS_BLOCKED);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_user_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_admin/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    public function setRoles(User $user, EntityManagerInterface $entityManager): void
    {
        $roles = $user->getRoles();
        if (is_array($roles) && !empty($roles)) {
            $roles = array_filter($roles, fn($item) => $item != User::USER_ROLE);
            $existsRoles =
           $user->setRoles($entityManager->getRepository(Role::class)->findAllByNames($roles));

        }
    }
}
