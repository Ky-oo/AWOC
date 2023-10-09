<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HamlayesController extends AbstractController
{
    #[Route('/hamladioui', name: 'app_hamlayes')]
    public function index(): Response
    {
        return $this->render('hamlayes/index.html.twig', [
            'controller_name' => 'HamlayesController',
        ]);
    }
}
