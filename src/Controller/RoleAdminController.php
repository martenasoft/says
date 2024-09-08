<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/admin/role')]
class RoleAdminController extends AbstractController
{
    #[Route('/', name: 'app_role_admin_index', methods: ['GET'])]
    public function index(
        RoleRepository $roleRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response {
        $queryBuilder = $roleRepository->getAllQueryBuilder();
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1)
        );

        return $this->render('role_admin/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_role_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($role);
            $entityManager->flush();
            $this->addFlash('success', 'Role created successfully');
            return $this->redirectToRoute('app_role_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('role_admin/new.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_admin_show', methods: ['GET'])]
    public function show(Role $role): Response
    {
        return $this->render('role_admin/show.html.twig', [
            'role' => $role,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_role_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Role $role, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Role changed successfully');
            return $this->redirectToRoute('app_role_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('role_admin/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Role $role, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$role->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($role);
            $this->addFlash('success', 'Page deleted successfully');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }
}
