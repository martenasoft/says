<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/{_locale}/profile')]
class UserController extends AbstractController
{
    #[Route('/{id}', name: 'app_user_profile', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/change-password/{id}', name: 'app_user_profile_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request                     $request,
        User                        $user,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $userPasswordHasher

    ): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Encode(hash) the plain password, and set it.
            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/change_password.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_user_profile_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $isAdmin = in_array(User::ADMIN_ROLE, $this->getUser()->getRoles());
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            if ($user->getStatus() !== User::STATUS_DELETED) {
                $user->setStatus(User::STATUS_DELETED);
            } elseif ($isAdmin) {
                $entityManager->remove($user);
            }

            if (!$isAdmin) {
                return $this->redirectToRoute('app_logout');
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
