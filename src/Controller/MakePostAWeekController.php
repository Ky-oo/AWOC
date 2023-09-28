<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MakePostAWeekController extends AbstractController
{
    #[Route('/make/post/a/week', name: 'app_make_post_a_week')]
    public function index(EntityManagerInterface $doctrine): Response
    {

        $postsUser = $doctrine->getRepository(Post::class)->findBy(['user'=>$this->getUser()]);

        dd($postsUser);



        return $this->render('make_post_a_week/index.html.twig', [
            'controller_name' => 'MakePostAWeekController',
        ]);
    }
}
