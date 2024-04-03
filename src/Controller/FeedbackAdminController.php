<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/feedback')]
class FeedbackAdminController extends AbstractController
{
    #[Route('/admin', name: 'app_feedback_admin_index', methods: ['GET'])]
    public function index(
        PaginatorInterface $paginator,
        Request            $request,
        FeedbackRepository $feedbackRepository
    ): Response
    {
        $queryBuilder = $feedbackRepository->getAllQueryBuilder();

        $pagination = $paginator->paginate(
        $queryBuilder->getQuery(),
        $request->query->getInt('page', 1)
    );
        return $this->render('feedback_admin/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/admin/new', name: 'app_feedback_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $feedback = new Feedback();
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feedback);
            $entityManager->flush();

            return $this->redirectToRoute('app_feedback_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('feedback_admin/new.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_feedback_admin_show', methods: ['GET'])]
    public function show(Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        $feedback->setStatus(Feedback::STATUS_VIEWED);
        $entityManager->flush();

        return $this->render('feedback_admin/show.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/admin/edit/{id}', name: 'app_feedback_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FeedbackType::class, $feedback);
        self::setDefaults($feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($feedback->getUpdatedAt())) {
                $feedback->setUpdatedAt(new \DateTimeImmutable('now'));
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_feedback_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('feedback_admin/edit.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/admin/delete/{id}', name: 'app_feedback_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$feedback->getId(), $request->getPayload()->get('_token'))) {
            if ($feedback->getStatus() !== Feedback::STATUS_DELETED) {
                $feedback->setStatus(Feedback::STATUS_DELETED);
            } else {
                $entityManager->remove($feedback);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_feedback_admin_index', [], Response::HTTP_SEE_OTHER);
    }

    public static function setDefaults(Feedback $feedback)
    {
        if (empty($feedback->getCreatedAt())) {
            $feedback->setCreatedAt(new \DateTimeImmutable('now'));
        }

        if (empty($feedback->getStatus())) {
            $feedback->setStatus(Feedback::STATUS_NEW);
        }
    }
}
