<?php

namespace App\Controller;

use App\Entity\Archive;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use DateInterval;
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
    public function index(PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager, LikeRepository $likeRepository): Response
    {
       $user = $this->getUser();
        $recentPost = null;
        $currentDateTime = new \DateTime('now');
        $postDateTime = null;
        $posts = $postRepository->findAll();
        $postsUser = $entityManager->getRepository(Post::class)->findBy(['user'=>$this->getUser()]);
        $like = null;
        $didUserLike = false;

        $archivedPost = $entityManager->getRepository(Archive::class)->findAll();
        foreach ($posts as $poste) {
            $posteDateTime = $poste->getDateTime();
            $sevenDaysAgo = (new \DateTime())->sub(new DateInterval('P7D'));

            if ($posteDateTime < $sevenDaysAgo) {
                if ($archivedPost != []) {

                    foreach ($archivedPost as $archives) {
                    if ($poste->getId() != $archives->getPost()->getId()) {
                        $archive = new Archive();
                        $archive->setPost($poste);
                        $poste->setIsArchived(true);
                        $entityManager->persist($archive);
                        $entityManager->persist($poste);
                        $entityManager->flush();
                    }
                }
            } else {

                    $archive = new Archive();
                    $archive->setPost($poste);
                    $poste->setIsArchived(true);
                    $entityManager->persist($archive);
                    $entityManager->persist($poste);
                    $entityManager->flush();

                }
            }

        }


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
            'posts' => $posts,
            'user' => $user,
            'delais' => $delais,
            'like' => $like,
            'didUserLike' => $didUserLike,
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
    {

        $recentPost = null;
        $currentDateTime = new \DateTime('now');
        $postDateTime = null;
        $post = null;
        $roleUser = false;

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
                    $post->setIsArchived(false);
                    $entityManager->persist($post);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
            }

        }



        foreach ($this->getUser()->getRoles() as $role){
            if($role == 'ROLE_SUPER_ADMIN'){
                $roleUser = true;
                $post = new Post();
                $timezone = new DateTimeZone('Europe/Paris');
                $post->setDateTime(new \DateTime('now', $timezone));
                $post->setUser($this->getUser());
                $form = $this->createForm(PostType::class, $post);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $post->setIsArchived(false);
                    $entityManager->persist($post);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
                }
            }
        }

        $test = true;

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form ?? null,
            'posts' => $postRepository->findAll(),
            'user' => $roleUser,
            'test' => $test,
        ]);
    }

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

    #[Route('/post/like/{id}', name: 'like_post', methods: ['POST'])]
    public function like(LikeRepository $likeRepository, Post $post, EntityManagerInterface $entityManager): Response
    {
        $allLike = $likeRepository->findBy(['user' => $this->getUser()->getId(), 'post' => $post->getId()]);
        if($allLike == null){
            $like = new Like();
            $like->setUser($this->getUser());
            $like->setPost($post);

            $entityManager->persist($like);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/post/unlike/{id}', name: 'unlike_post', methods: ['POST'])]
    public function unlike(LikeRepository $likeRepository, Post $post, EntityManagerInterface $entityManager): Response
    {

        $entityManager->remove($likeRepository->findOneBy(['user' => $this->getUser()->getId(), 'post' => $post->getId()]));
        $entityManager->flush();

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

}
