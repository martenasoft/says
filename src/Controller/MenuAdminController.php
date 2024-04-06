<?php

namespace App\Controller;


use App\Controller\Traits\RebuildMenuPathTrait;
use App\Entity\Interfaces\NodeInterface;
use App\Entity\Menu;
use App\Form\MenuType;
use App\Helper\StringHelper;
use App\Repository\MenuRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuAdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface        $logger
    )
    {
    }

    #[Route('/admin/menu', name: 'menu_admin_index')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        return $this->render('menu_admin/index.html.twig');
    }

    #[Route('/admin/menu/new', name: 'menu_admin_new_menu')]
    public function newRoot(
        Request         $request,
        MenuRepository  $menuRepository,
        LoggerInterface $logger
    ): Response
    {
        $menu = new Menu();
        $menu->setStatus(Menu::STATUS_ACTIVE);
        $menu->setCreatedAt(new \DateTimeImmutable('now'));

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $this->setDefaults($menu);
                $menuRepository->create($menu);
                $this->addFlash('success', 'Menu created successfully');
                return $this->redirectToRoute('menu_admin_index');
            } catch (\Throwable $exception) {
                $message = "001501. Error create menu!";
                if ($exception instanceof UniqueConstraintViolationException) {
                    $message = "001501. Menu with name [{$menu->getName()}] already exists!";
                }
                $this->addFlash("error", $message);
                $logger->error($exception->getMessage(), [
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile()
                ]);
            }
        }

        return $this->render('menu_admin/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/menu/new-sub-menu/{id}', name: 'menu_admin_new_sub_menu')]
    public function newSubMenu(
        Request                $request,
        Menu                   $parent,
        MenuRepository         $menuRepository,
        LoggerInterface        $logger,
        EntityManagerInterface $entityManager
    ): Response
    {
        $menu = new Menu();
        $menu->setStatus(Menu::STATUS_ACTIVE);
        $menu->setCreatedAt(new \DateTimeImmutable('now'));

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->beginTransaction();
            try {
                $this->setDefaults($menu);
                $menuRepository->create($menu, $parent);
                $entityManager->flush();
                $entityManager->commit();
                $this->addFlash('success', 'Menu updated successfully');
                return $this->redirectToRoute('menu_admin_index');
            } catch (\Throwable $exception) {
                $message = "001502. Error create sub menu!";
                if ($exception instanceof UniqueConstraintViolationException) {
                    $message = "0011502. Menu with name [{$menu->getName()}] already exists!";
                }
                $this->addFlash("error", $message);
                $logger->error($exception->getMessage(), [
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile()
                ]);
                $entityManager->rollback();
            }
        }

        return $this->render('menu_admin/edit.html.twig', [
            'form' => $form->createView(),
            'menuItem' => $parent
        ]);
    }

    #[Route('/admin/menu/edit/{id}', name: 'menu_admin_edit')]
    public function edit(
        Request                $request,
        Menu                   $menu,
        MenuRepository         $menuRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface        $logger
    ): Response
    {
        $menu->setStatus(Menu::STATUS_ACTIVE);
        $menu->setUpdatedAt(new \DateTimeImmutable('now'));
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $entityManager->beginTransaction();
                $this->setDefaults($menu);;
                $entityManager->flush();
                $entityManager->commit();
                $this->addFlash('success', 'Menu updated successfully');
                return $this->redirectToRoute('menu_admin_edit', ['id' => $menu->getId()]);
            } catch (\Throwable $exception) {
                $message = "001503. Error updated menu!";
                if ($exception instanceof UniqueConstraintViolationException) {
                    $message = "0011503. Menu with name [{$menu->getName()}] already exists!";
                }
                $this->addFlash("error", $message);
                $logger->error($exception->getMessage(), [
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile()
                ]);
                $entityManager->rollback();
            }
        }

        return $this->render('menu_admin/edit.html.twig', [
            'form' => $form->createView(),
            'menuItem' => $menu
        ]);
    }

    public function treeMenuAdmin(
        Request            $request,
        PaginatorInterface $paginator,
        MenuRepository     $menuRepository
    ): Response
    {
        $pagination = $paginator->paginate(
            $menuRepository->getAllQueryBuilder()->getQuery(),
            $request->query->getInt('page', 1)
        );

        return $this->render('menu_admin/tree_menu_admin.html.twig', [
                'request' => $request,
                'pagination' => $pagination]
        );
    }

    #[Route('/admin/menu/up/{id}', name: 'menu_admin_up')]
    public function up(
        Menu                   $menu,
        MenuRepository         $menuRepository,
        Request                $request,
        LoggerInterface        $logger,
        EntityManagerInterface $entityManager
    ): Response
    {
        $oldNode = $newNode = null;
        try {
            $entityManager->beginTransaction();
            $menuRepository->upDown($menu, true);

            $entityManager->flush();
            $entityManager->commit();
            $this->addFlash('success', 'Menu moved up successfully');
        } catch (\Throwable $exception) {
            $this->addFlash("error", "001505. Error updated moved up!");
            $logger->error($exception->getMessage(), [
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile()
            ]);
            $entityManager->rollback();
        }

        return $this->redirectToRoute('menu_admin_edit', [
            'id' => $menu->getId(),
            'page' => $request->request->getInt('page', 1)
        ]);
    }

    #[Route('/admin/menu/down/{id}', name: 'menu_admin_down')]
    public function down(
        Menu                   $menu,
        MenuRepository         $menuRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
        LoggerInterface        $logger
    ): Response
    {
        try {
            $entityManager->beginTransaction();
            $menuRepository->upDown($menu, false);
            $entityManager->commit();
            $this->addFlash("success", "Menu moved down successfully");
        } catch (\Throwable $exception) {
            $this->addFlash("error", "001506. Error updated moved down!");
            $logger->error($exception->getMessage(), [
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile()
            ]);
            $entityManager->rollback();
        }

        return $this->redirectToRoute('menu_admin_edit', [
            'id' => $menu->getId(),
            'page' => $request->request->getInt('page', 1)
        ]);
    }

    #[Route('/admin/menu/delete/{id}', name: 'menu_admin_delete')]
    public function delete(
        Menu                   $menu,
        MenuRepository         $menuRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
        LoggerInterface        $logger
    ): Response
    {
        try {
            $entityManager->beginTransaction();
            $menuRepository->delete($menu);
            $this->addFlash('success', 'Menu deleted successfully');
            $entityManager->commit();
        } catch (\Throwable $exception) {
            $this->addFlash("error", "001507. Error delete menu!");
            $logger->error($exception->getMessage(), [
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile()
            ]);
            $entityManager->rollback();
        }
        return $this->redirectToRoute('menu_admin_index', [
            'id' => $menu->getId(),
            'page' => $request->request->getInt('page', 1)
        ]);
    }

    private function setDefaults(Menu $menu): void
    {
        $path = htmlspecialchars(addslashes($menu->getSlug()));
        $menu->setPath($path);

        if (empty($menu->getSlug())) {
            $menu->setSlug(StringHelper::slug($menu->getName()));
        }
    }
}
