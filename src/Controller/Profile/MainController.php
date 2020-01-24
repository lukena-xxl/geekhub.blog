<?php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/profile", name="profile_main")
     */
    public function index()
    {
        return $this->render('profile/main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
