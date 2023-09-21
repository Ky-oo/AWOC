<?php

namespace App\Controller;

use App\Form\FormTestType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(Request $request): Response
    {
        $testValide = false;
        $phrase = "";

        $myForm = $this->createForm(FormTestType::class);
        $myForm->handleRequest($request);

        if($myForm->isSubmitted() && $myForm->isValid()){
            $data = $myForm->getData();
            $nom = $data["nom"];
            if($data["liste_choix"]){
                $phrase = $nom . ", tu aime les formulaires!";
            } else {
                $phrase = $nom . ", tu n'aime pas les formulaires!";
            }
            $testValide = true;
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'myForm' => $myForm,
            'testValide' => $testValide,
            'phrase' => $phrase
        ]);
    }
}
