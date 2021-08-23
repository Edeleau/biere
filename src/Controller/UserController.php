<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Snappy\Pdf;
use App\Entity\User;
use App\Form\UserType;
use App\Security\UserVoter;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    public function delete(Request $request, TokenStorageInterface $tokenStorage, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }
        $request->getSession()->invalidate();
        $tokenStorage->setToken();


        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/order/{order}", name="user_order" , requirements={"order":"\d+" })
     */
    public function order(UserRepository $userRepository, OrderRepository $orderRepository, int $order): Response
    {

        $order = $orderRepository->findOneBy(['id' => $order]);
        $user = $userRepository->findOneBy(['id' => $order->getUser()->getId()]);
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);
        if ($order === null) {
            return $this->redirectToRoute('user_show');
        }

        return $this->render('user/order.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/order/generate/{order}", name="pdf_generate" , requirements={"order":"\d+" })
     */
    public function pdfAction(UserRepository $userRepository, OrderRepository $orderRepository, int $order)
    {
        $order = $orderRepository->findOneBy(['id' => $order]);
        $user = $userRepository->findOneBy(['id' => $order->getUser()->getId()]);
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);
        if ($order === null) {
            return $this->redirectToRoute('user_show');
        }

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('user/order_pdf.html.twig', [
            'order' => $order,
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }
}
