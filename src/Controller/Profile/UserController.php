<?php

namespace App\Controller\Profile;

use App\Entity\Users;
use App\Form\Users\UserAddType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UserController
 * @package App\Controller\Profile
 * @Route("/profile", name="profile")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Users $user
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function editUser(Users $user, Request $request, EntityManagerInterface $entityManager)
    {
        if ($user->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException('You do not have access!');
        }

        $form = $this->createForm(UserAddType::class, $user, [
            'action' => $this->generateUrl('profile_edit', ['id' => $user->getId()]),
            'method' => 'post',
        ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'label' => 'Password',
                'help' => 'Password must contain numbers and letters. If you do not need to change the password, leave this field blank',
                'attr' => [
                    'placeholder' => 'Enter password',
                ],
                'empty_data' => $user->getPassword(),
            ])
            ->remove('status');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $message = "User information has been successfully changed!";
            $this->addFlash('success', $message);
        }

        return $this->render('profile/user/edit.html.twig', [
            'controller_name' => 'UserController',
            'form_add' => $form->createView(),
        ]);
    }
}
