<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isNull;
use function Symfony\Component\Clock\now;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

       $user = $this->getUser();

        $recentPost = null;
        $currentDateTime = new \DateTime('now');
        $postDateTime = null;
        $postsUser = $entityManager->getRepository(Post::class)->findBy(['user'=>$this->getUser()]);

        foreach ($postsUser as $postByUser){

            $postDateTime = $postByUser->getDateTime();

            if ($recentPost === null || $postDateTime > $recentPost->getDateTime()) {
                $recentPost = $postByUser;
            }

        }

        if(!isset($postDateTime)){

            $postDateTime = new \DateTime('2001-01-01');
        }

        $interval = $currentDateTime->diff($postDateTime);
        $daysDifference = $interval->format('%a');


        if ($daysDifference > 7) {

            $delais = false;

        } else {
            $delais = true;
        }


        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
            'user' => $user,
            'delais' => $delais
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
    {

        $recentPost = null;
        $currentDateTime = new \DateTime('now');
        $delais = false;
        $postDateTime = null;
        $post = null;

        $postsUser = $entityManager->getRepository(Post::class)->findBy(['user'=>$this->getUser()]);

        foreach ($postsUser as $postByUser){

            $postDateTime = $postByUser->getDateTime();

            if ($recentPost === null || $postDateTime > $recentPost->getDateTime()) {
                $recentPost = $postByUser;
            }

        }

        if(!isset($postDateTime)){

            $postDateTime = new \DateTime('2001-01-01');
        }

        $interval = $currentDateTime->diff($postDateTime);
        $daysDifference = $interval->format('%a');


        if ($daysDifference > 7) {



            $post = new Post();
            $timezone = new DateTimeZone('Europe/Paris');
            $post->setDateTime(new \DateTime('now', $timezone));
            $post->setUser($this->getUser());
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager->persist($post);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
            }

        } else {
            $delais = true;
        }



        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form ?? null,
            'posts' => $postRepository->findAll(),
        ]);}




    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
