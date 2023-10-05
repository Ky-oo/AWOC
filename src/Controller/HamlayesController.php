<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HamlayesController extends AbstractController
{
    #[Route('/hamlayes', name: 'app_hamlayes')]
    public function index(): Response
    {
        return $this->render('hamlayes/index.html.twig', [
            'controller_name' => 'HamlayesController',
        ]);
    }
}
