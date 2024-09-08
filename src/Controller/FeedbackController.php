<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackFrontType;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\LocaleSwitcher;

#[Route('/{_locale}/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/', name: 'app_feedback_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        PageRepository $pageRepository,
        LocaleSwitcher $localeSwitcher
    ): Response {
        $page = $pageRepository
            ->getOneBySlugQueryBuilder('app_feedback_new', true)
            ->getQuery()
            ->getOneOrNullResult();
        $feedback = new Feedback();
        $feedback->setLang($localeSwitcher->getLocale());
        $user = $this->getUser();
        if (!empty($user) ) {
            $feedback->setFromEmail($user->getUserIdentifier());
        }
        FeedbackAdminController::setDefaults($feedback);
        $form = $this->createForm(FeedbackFrontType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feedback);
            $entityManager->flush();

            return $this->redirectToRoute('app_feedback_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('feedback/new.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
            'page' => $page
        ]);
    }
}
