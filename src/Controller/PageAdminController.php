<?php

namespace App\Controller;

use App\Controller\Traits\PageTrait;
use App\Entity\Page;
use App\Form\PageType;
use App\Helper\StringHelper;
use App\Repository\PageRepository;
use App\Service\SaveImageService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Monolog\DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

        if (empty($page->getPosition())) {
            $page->setPosition(9999);
        }

        if (empty($page->getSlug()) && !empty($page->getName())) {
            $page->setSlug(StringHelper::slug($page->getName()));
        }

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $saveImageService->upload($form, $page);
            $parent = (!empty($page) && !empty($page->getParent()) ? $page->getParent()->getLevel() : 0);
            $page->setLevel(++$parent);
            $entityManager->persist($page);
            $entityManager->flush();

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

        if (empty($page->getSlug()) && !empty($page->getName())) {
            $page->setSlug(StringHelper::slug($page->getName()));
        }
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $saveImageService->upload($form, $page);
            $entityManager->flush();

            return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('page_admin/edit.html.twig', [
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

}
