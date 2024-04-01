<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\User;
use App\Form\PageType;
use App\Helper\StringHelper;
use App\Repository\PageRepository;
use App\Service\SaveImageService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Monolog\DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class PageController extends AbstractController
{
    #[Route('/', name: 'app_page_main', methods: ['GET'])]
    public function main(
        PageRepository     $pageRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response
    {
        return $this->render('page/index.html.twig', [
            'pagination' => $this->getPagination($pageRepository, $paginator, $request)
        ]);
    }

    #[Route('/page/{slug}.html', name: 'app_page_show', methods: ['GET'])]
    public function show(Page $page): Response
    {
        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/page/{slug}', name: 'app_page_section', methods: ['GET'])]
    public function section(
        Page               $page,
        PageRepository     $pageRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response
    {
        return $this->render('page/index.html.twig', [
            'pagination' => $this->getPagination($pageRepository, $paginator, $request),
        ]);
    }

    /*
     * Admin Panel
     */

    #[Route('/admin/page', name: 'app_page_index', methods: ['GET'])]
    public function index(
        PageRepository     $pageRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response
    {
        return $this->render('page/index.html.twig', [
            'pagination' => $this->getPagination($pageRepository, $paginator, $request),
        ]);
    }

    #[Route('/admin/page/new', name: 'app_page_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        SaveImageService       $saveImageService
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

        if (empty($page->getType())) {
            $page->setType(Page::PAGE_TYPE);
        }

        if (empty($page->getSlug())) {
            $page->setSlug(StringHelper::slug($page->getName()));
        }

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parent = $page->getParent()->getLevel() ?? 0;
            $page->setLevel(++$parent);
            $entityManager->persist($page);
            $entityManager->flush();

            return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('page/new.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/admin/page/edit/{id}', name: 'app_page_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                $request,
        Page                   $page,
        EntityManagerInterface $entityManager,
        SaveImageService       $saveImageService
    ): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $dateTimeNow = new DateTimeImmutable('now');

        if (empty($page->getUpdatedAt())) {
            $page->setUpdatedAt($dateTimeNow);
        }

        if (empty($page->getType())) {
            $page->setType(Page::PAGE_TYPE);
        }

        if (empty($page->getSlug())) {
            $page->setSlug(StringHelper::slug($page->getName()));
        }
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $saveImageService->upload($form, $page);
            $entityManager->flush();

            return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('page/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/admin/page/delete/{id}', name: 'app_page_delete', methods: ['POST'])]
    public function delete(Request $request, Page $page, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->getPayload()->get('_token'))) {
            if ($page->getStatus() !== Page::STATUS_DELETED) {
                $page->setStatus(Page::STATUS_DELETED);
            } else {
                $entityManager->remove($page);
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    public function menu(string $type, PageRepository $pageRepository): Response
    {
        $queryBuilder = $this
            ->getItemsQueryBuilder($pageRepository)
            ->leftJoin("p.parent", "pp")
            ->addSelect("pp")
            ->andWhere("p.menuType IN (:menuType)")
            ->setParameter('menuType', [
                Page::MENU_TYPE_BOTH_VERTICAL_AND_HORIZONTAL,
                $type
            ]);

        return $this->render('page/menu.html.twig', [
            'type' => $type,
            'items' => $queryBuilder->getQuery()->getResult()
        ]);
    }

    public function breadCrumbs(Page $page, PageRepository $pageRepository): Response
    {
        return $this->render('page/bread_crumbs.html.twig', [
            'items' => $pageRepository->breadCrumbs($page),
            'page' => $page
        ]);
    }

    private function getPagination(
        PageRepository     $pageRepository,
        PaginatorInterface $paginator,
        Request            $request,
        int                $status = Page::STATUS_ACTIVE
    ): PaginationInterface
    {
        $queryBuilder = $this->getItemsQueryBuilder($pageRepository);
        $user = $this->getUser();

        if (!in_array(User::ADMIN_ROLE, $user->getRoles())) {
            $queryBuilder
                ->andWhere("p.status=:status")
                ->setParameter('status', $status)
                ->andWhere("p.publicAt<=:now")
                ->setParameter("now", (new DateTimeImmutable('now')));
        }

        //
        return $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1)
        );
    }

    private function getItemsQueryBuilder(PageRepository $pageRepository): QueryBuilder
    {
        return $pageRepository
            ->getAllQueryBuilder()
            ->orderBy("p.publicAt", "DESC")
            ->addOrderBy('p.position', "ASC");

    }

}
