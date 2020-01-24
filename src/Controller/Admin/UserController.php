<?php

namespace App\Controller\Admin;

use App\Entity\Statuses;
use App\Entity\Users;
use App\Form\Users\UserAddType;
use App\Form\Users\UserSortType;
use App\Form\Users\UserTargetType;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller\Admin
 * @Route("/admin/user", name="admin_user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function showAllUsers(Request $request, EntityManagerInterface $entityManager)
    {
        $formSort = $this->createForm(UserSortType::class, null, [
            'action' => $this->generateUrl('admin_user_show_all'),
            'method' => 'get',
        ]);

        $formSort->handleRequest($request);
        $arguments = [];

        if ($formSort->isSubmitted() && $formSort->isValid()) {
            $formDataSort = $formSort->getData();

            $arguments['num'] = $formDataSort['num'];
            $arguments['symbol'] = $formDataSort['symbol'];

            if (!empty($formDataSort['category'])) {
                $arguments['category'] = $formDataSort['category'];
            }
        }

        $usersRepository = $entityManager->getRepository(Users::class);

        if (count($arguments) > 0) {
            $users = $usersRepository->findBySort($arguments);
        } else {
            $users = $usersRepository->findAll();
        }

        $formTarget = $this->createForm(UserTargetType::class, null, [
            'action' => $this->generateUrl('admin_user_show_all'),
            'method' => 'get',
        ]);

        $formTarget->handleRequest($request);

        if ($formTarget->isSubmitted() && $formTarget->isValid()) {
            $formDataTarget = $formTarget->getData();

            $user = $formDataTarget['user'];
            $usersRepository->resetTargetUser();

            if ($user) {
                $user->setTarget(1);
                $entityManager->persist($user);
                $entityManager->flush();
            }
        } else {
            $user = $usersRepository->findOneBy(['target' => 1]);
        }

        return $this->render('admin/users/show_all.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
            'target_user' => $user,
            'form_sort' => $formSort->createView(),
            'form_target' => $formTarget->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param Users $user
     * @return Response
     */
    public function showUser(Users $user)
    {
        return $this->render('admin/users/show.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
        ]);
    }

    /**
     * @Route("/add", name="_add")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param UpdateManager $updateManager
     * @return Response
     * @throws Exception
     */
    public function addUser(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, UpdateManager $updateManager)
    {
        $form = $this->createForm(UserAddType::class, null, [
            'action' => $this->generateUrl('admin_user_add'),
            'method' => 'post',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $user->setRegistrationDate(new \DateTime());
            $user->setRoles(["ROLE_" . $user->getStatus()->getTitle()]);

            $entityManager->persist($user);
            $entityManager->flush();

            $message = "New user \"" . $user->getLogin() . "\" with status \"" . $user->getStatus()->getTitle() . "\" has been added";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('admin_user_show_all');
        }

        return $this->render('admin/users/add.html.twig', [
            'controller_name' => 'UserController',
            'form_add' => $form->createView(),
            'title' => 'Adding a user',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Users $user
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function editUser(Users $user, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(UserAddType::class, $user, [
            'action' => $this->generateUrl('admin_user_edit', ['id' => $user->getId()]),
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
                ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(["ROLE_" . $user->getStatus()->getTitle()]);

            $entityManager->persist($user);
            $entityManager->flush();

            $message = "User information has been successfully changed!";
            $this->addFlash('success', $message);
        }

        return $this->render('admin/users/add.html.twig', [
            'controller_name' => 'UserController',
            'form_add' => $form->createView(),
            'title' => 'Editing user "' . $user->getLogin() . '"',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param Users $user
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @return Response
     */
    public function deleteUser(Users $user, UpdateManager $updateManager, LoggerInterface $logger)
    {
        if ($user->getArticles()->count() > 0) {
            $this->addFlash('warning', 'This user has added publications. Unlink these publications to this user and try deleting again!');
            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        } else {
            $favoriteArticles = $user->getFavoriteArticles();
            if ($favoriteArticles) {
                foreach ($favoriteArticles as $favoriteArticle) {
                    $user->removeFavoriteArticle($favoriteArticle);
                }
            }

            $favoriteUsers = $user->getFavoriteUsers();
            if ($favoriteUsers) {
                foreach ($favoriteUsers as $favoriteUser) {
                    $user->removeFavoriteUser($favoriteUser);
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $message = "User \"" . $user->getLogin() . "\" has been deleted";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('admin_user_show_all');
        }
    }
}
