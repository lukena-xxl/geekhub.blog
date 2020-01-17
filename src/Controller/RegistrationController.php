<?php

namespace App\Controller;

use App\Entity\Statuses;
use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @param LoggerInterface $logger
     * @param UpdateManager $updateManager
     * @return Response
     * @throws Exception
     */
    public function register(Request $request, EntityManagerInterface $entityManager, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, LoggerInterface $logger, UpdateManager $updateManager): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRegistrationDate(new \DateTime());

            $status = $entityManager->getRepository(Statuses::class)->find(1);
            if ($status) {
                $user->setStatus($status);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $message = "New user registered: \"" . $user->getLogin() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main'
            );
        }

        return $this->render('registration/register.html.twig', [
            'controller_name' => 'RegistrationController',
            'registrationForm' => $form->createView(),
        ]);
    }
}
