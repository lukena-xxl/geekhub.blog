<?php

namespace App\Controller;

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
 * @package App\Controller
 * @Route("/user", name="user")
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
            'action' => $this->generateUrl('user_show_all'),
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
            'action' => $this->generateUrl('user_show_all'),
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

        return $this->render('users/show_all.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
            'target_user' => $user,
            'form_sort' => $formSort->createView(),
            'form_target' => $formTarget->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showUser($id)
    {
        return $this->render('users/show.html.twig', [
            'controller_name' => 'UserController',
            'user' => $this->findUser($id),
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
            'action' => $this->generateUrl('user_add'),
            'method' => 'post',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $user = new Users();
            $user->setName($formData['name']);
            $user->setLogin($formData['login']);
            $user->setPassword($formData['password']);
            $user->setRegistrationDate(new \DateTime());
            $user->setBirthDate($formData['birth_date']);
            $user->setGender($formData['gender']);

            $status = $entityManager->getRepository(Statuses::class)->find($formData['status']);
            if ($status) {
                $user->setStatus($status);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $message = "Добавлен новый пользователь \"" . $user->getLogin() . "\" со статусом \"" . $user->getStatus()->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('user_show_all');
        }

        return $this->render('users/add.html.twig', [
            'controller_name' => 'UserController',
            'form_add' => $form->createView(),
            'title' => 'Добавление пользователя',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @return Response
     */
    public function editUser(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $user = $this->findUser($id);

        $form = $this->createForm(UserAddType::class, $user, [
            'action' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
            'method' => 'post',
            ])
            ->remove('password')
            ->add('password', PasswordType::class, [
                'required' => false,
                'label' => 'Пароль',
                'help' => 'Пароль должен содержать цифры и буквы',
                'attr' => [
                    'placeholder' => 'Введите пароль',
                    ],
                'empty_data' => $user->getPassword(),
                ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $entityManager->persist($formData);
            $entityManager->flush();

            $message = "Информация о пользователе была успешно изменена!";
            $this->addFlash('success', $message);
        }

        return $this->render('users/add.html.twig', [
            'controller_name' => 'UserController',
            'form_add' => $form->createView(),
            'title' => 'Редактирование пользователя "' . $user->getLogin() . '"',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @param $id
     * @return Response
     */
    public function deleteUser(UpdateManager $updateManager, LoggerInterface $logger, $id)
    {
        $user = $this->findUser($id);

        if ($user->getArticles()->count() > 0) {
            throw $this->createNotFoundException(
                'К пользователю привязаны некоторые статьи. Уберите привязку и повторите попытку удаления!'
            );
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

            $message = "Пользователь \"" . $user->getLogin() . "\" был удален";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('user_show_all');
        }
    }

    /**
     * @param $id
     * @return Users
     */
    private function findUser($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(Users::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'Пользователь с идентификатором "' . $id . '" не найден!'
            );
        } else {
            return $user;
        }
    }
}
