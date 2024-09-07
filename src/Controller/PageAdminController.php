<?php

namespace App\Controller;

use App\Controller\Traits\PageTrait;
use App\Entity\Interfaces\SeoInterface;
use App\Entity\Menu;
use App\Entity\Page;
use App\Error\Interfaces\ErrorCodesInterface;
use App\Form\PageType;
use App\Helper\StringHelper;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use App\Service\SaveImageService;
use App\Service\SaveMenuService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Monolog\DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatableInterface;

class PageAdminController extends AbstractController
{
    use PageTrait;

    #[Route('/admin/page', name: 'app_page_index', methods: ['GET'])]
    public function index(
        PageRepository     $pageRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response
    {

        return $this->render('page_admin/index.html.twig', [
            'pagination' => $this->getPagination($pageRepository, $paginator, $request),
        ]);
    }

    #[Route('/admin/page/new', name: 'app_page_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        SaveImageService       $saveImageService,
        MenuRepository         $menuRepository,
        LoggerInterface        $logger
    ): Response
    {
        $page = new Page();
        $dateTimeNow = new DateTimeImmutable('now');

        if (empty($page->getCreatedAt())) {
            $page->setCreatedAt($dateTimeNow);
        }

        if (empty($page->getPublicAt())) {
            $page->setPublicAt($dateTimeNow);
        }

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->beginTransaction();
            try {
                $this->setDefaults($page);
                $menu = $this->initNewMenu($page);

                if ($menu !== null) {
                    $menuRepository->create($menu, $menu->getParent());
                }

                $page->setMenu($menu);
                $saveImageService->upload($form, $page);
                $entityManager->persist($page);
                $entityManager->flush();

                $this->addFlash('success', 'Page created successfully');
                $entityManager->commit();
            } catch (\Throwable $exception) {
                $this->addFlash("error", "000501. Error create page!");
                $logger->error($exception->getMessage(), [
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile()
                ]);
                $entityManager->rollback();
            }

            return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('page_admin/new.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/admin/page/edit/{id}', name: 'app_page_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                $request,
        int                    $id,
        EntityManagerInterface $entityManager,
        SaveImageService       $saveImageService,
        PageRepository         $pageRepository,
        MenuRepository         $menuRepository,
        LoggerInterface        $logger
    ): Response
    {
        $page = $pageRepository->getOneById($id);
        $menu = $page->getMenu();
        $parentMenu = $menuRepository->getOneParent($menu);

        if ($menu !== null) {
            $menu->setParent($parentMenu);
        }

        $form = $this->createForm(PageType::class, $page);
        $dateTimeNow = new DateTimeImmutable('now');

        if (empty($page->getUpdatedAt())) {
            $page->setUpdatedAt($dateTimeNow);
        }

        if (empty($page->getType())) {
            $page->setType(Page::PAGE_TYPE);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->beginTransaction();
            try {
                // set default values
                $this->setDefaults($page);

                // get selected menu in form
                $selectedMenu = $page->getMenu();
                // delete menu item
                if (!empty($menu) && empty($selectedMenu)) {
                    $menuRepository->delete($menu);

                }

                // create menu
                if (empty($menu) && !empty($selectedMenu)) {
                    $newMenu = $this->initNewMenu($page);
                    $menuRepository->create($newMenu, $selectedMenu->getParent());
                    $menu = $newMenu;
                }

                // move element
                if (!empty($menu) && !empty($selectedMenu) && $menu !== $selectedMenu) {
                    $menuRepository->move($menu, $selectedMenu);
                    $menuRepository->updateUrlInSubElements($menu, $selectedMenu->getSlug());
                }

                if (!empty($menu)) {
                    $path = $menuRepository->getMenuPath($menu);
                    $menu->setPath($path);
                }

                //upload image
                $saveImageService->upload($form, $page);
                $this->addFlash('success', 'Page updated successfully');
                $entityManager->flush();
                $entityManager->commit();
            } catch (\Throwable $exception) {
                $logger->error($exception->getMessage(), [
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile()
                ]);
                $this->addFlash("error", "000503. Error updated page!");
                $entityManager->rollback();

            }

            return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('page_admin/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/admin/page/delete/{id}', name: 'app_page_delete', methods: ['POST'])]
    public function delete(
        Request                $request,
        Page                   $page,
        EntityManagerInterface $entityManager,
        MenuRepository         $menuRepository,
        LoggerInterface        $logger
    ): Response
    {
        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->beginTransaction();
            try {
                $menu = $page->getMenu();
                if (!empty($menu)) {
                    $page->setMenu(null);
                    $menuRepository->delete($menu, $page->getStatus() !== Page::STATUS_DELETED);
                }

                if ($page->getStatus() !== Page::STATUS_DELETED) {
                    $page->setStatus(Page::STATUS_DELETED);
                } else {

                    $entityManager->remove($page);
                }

                $entityManager->flush();
                $entityManager->commit();
                $this->addFlash('success', 'Page deleted successfully');

            } catch (\Throwable $exception) {
                $this->addFlash("error", "000504.Error delete page!");
                $logger->error($exception->getMessage(),  [
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile()
                ]);
                $entityManager->rollback();
            }
        }

        return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    private function setDefaults(Page $page): void
    {

        if (empty($page->getOgUrl()) && !empty($page->getSlug()) && $page->getSlug() !== Page::MAIN_URL) {
            $page->setOgTitle($page->getSlug());
        }


        if (empty($page->getSlug()) && !empty($page->getName())) {
            $page->setSlug(StringHelper::slug($page->getName()));
        }
    }

    private function initNewMenu(Page $page): ?Menu
    {
        $newMenu = $page->getMenu();
        if ($newMenu === null) {
            return null;
        }

        $newMenu
            ->setName($page->getName())
            ->setSlug($page->getSlug())
            ->setCreatedAt(new \DateTimeImmutable('now'))
            ->setType(Menu::ITEM_MENU_TYPE)
            ->setStatus(Menu::STATUS_ACTIVE);
        return $newMenu;
    }
}
