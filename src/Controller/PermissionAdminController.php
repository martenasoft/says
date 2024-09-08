<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Form\PermissionType;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/admin/permission')]
class PermissionAdminController extends AbstractController
{
    #[Route('/', name: 'app_permission_admin_index', methods: ['GET'])]
    public function index(
        PermissionRepository $permissionRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response {
        $queryBuilder = $permissionRepository->getAllQueryBuilder();
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1)
        );

        return $this->render('permission_admin/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_permission_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $permission = new Permission();
        $form = $this->createForm(PermissionType::class, $permission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($permission);
            $entityManager->flush();
            $this->addFlash('success', 'Permission created successfully');
            return $this->redirectToRoute('app_permission_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('permission_admin/new.html.twig', [
            'permission' => $permission,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_permission_admin_show', methods: ['GET'])]
    public function show(Permission $permission): Response
    {
        return $this->render('permission_admin/show.html.twig', [
            'permission' => $permission,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_permission_admin_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Permission $permission,
        EntityManagerInterface $entityManager,
        PermissionRepository $permissionRepository
    ): Response
    {
        $form = $this->createForm(PermissionType::class, $permission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $permissionRepository->saveCount($permission);
            $entityManager->flush();
            $this->addFlash('success', 'Page changed successfully');

            return $this->redirectToRoute('app_permission_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('permission_admin/edit.html.twig', [
            'permission' => $permission,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_permission_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Permission $permission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$permission->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($permission);
            $this->addFlash('success', 'Page deleted successfully');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_permission_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
