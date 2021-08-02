<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\UserVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/profil", name="user_show")
     */
    public function show(UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $this->getUser()->getId()]);
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/edit", name="user_edit")
     */
    public function edit(Request $request, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $this->getUser()->getId()]);
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $testMail = $userRepository->findOneBy(['email' => $form->getData()->getEmail()]);
            if ($testMail && $testMail->getId() !== $user->getId()) {
                $errors[] = 'Email déjà existant sur notre site.';
            }
            if (empty($errors)) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('user_show');
            } else {
                foreach ($errors as $error => $errorMsg) {
                    $this->addFlash('error', $errorMsg);
                }
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
