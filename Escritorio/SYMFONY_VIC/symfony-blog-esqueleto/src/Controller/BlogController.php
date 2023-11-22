<?php

namespace App\Controller;

use Symfony\Component\Filesystem\Filesystem;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;    
use Symfony\Component\HttpFoundation\File\UploadedFile;


class BlogController extends AbstractController
{
    #[Route("/blog/buscar/{page}", name: 'blog_buscar')]
    public function buscar(ManagerRegistry $doctrine,  Request $request, int $page = 1): Response
    {
        $searchTerm = $request->query->get('q');

    if (!$searchTerm) {
        // Handle the case when no search term is provided
        // You may want to redirect or display an error message
        return $this->redirectToRoute('blog');
    }

    $entityManager = $doctrine->getManager();
    $repository = $entityManager->getRepository(Post::class);

    // Use DQL (Doctrine Query Language) to fetch posts with titles containing the search term
    $query = $repository->createQueryBuilder('p')
        ->where('p.title LIKE :searchTerm')
        ->setParameter('searchTerm', '%' . $searchTerm . '%')
        ->orderBy('CASE WHEN p.title LIKE :exactSearchTerm THEN 1 ELSE 2 END', 'ASC')
        ->setParameter('exactSearchTerm', $searchTerm)
        ->getQuery();

    $posts = $query->getResult();

    return $this->render('blog.html.twig', [
        'posts' => $posts,
        'searchTerm' => $searchTerm,
    ]);}
    
   

    #[Route("/blog/new", name: 'new_post')]
    public function newPost(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->getUser(); // Obtener el usuario actual

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);


        // Establecer el número de likes, comentarios y vistas en 0 desde fuera
        $post->setNumLikes(0);
        $post->setNumComments(0);
        $post->setNumViews(0);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Establecer el usuario actual como el autor del post
            $post->setUser($user);

            // Generar el slug a partir del título
            $post->setSlug($slugger->slug($post->getTitle())->lower());

            // Manejar la imagen
            $file = $form->get('Image')->getData();
            if($file){
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
            $post->setImage($newFilename);
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/blog';
            $file->move($uploadDir, $newFilename);
        }

            // Guardar el post en la base de datos
            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            // Redirigir a la página de detalle del nuevo post, o a donde desees
            return $this->redirectToRoute('blog', ['slug' => $post->getSlug()]);
        }

        return $this->render('blog/new_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    


    
    #[Route("/single_post/{slug}/like", name: 'post_like')]
    public function like(ManagerRegistry $doctrine, $slug): Response
    {
        $entityManager = $doctrine->getManager();
        $postRepository = $entityManager->getRepository(Post::class);
        $likeRepository = $entityManager->getRepository(Like::class);
    
        // Find the post by its slug
        $post = $postRepository->findOneBy(['slug' => $slug]);
    
        if (!$post) {
            // Handle the case when the post is not found
            return $this->redirectToRoute('blog'); // Or display an error message
        }
    
        // Check if the user already liked the post (you may need to associate likes with users)
        $existingLike = $likeRepository->findOneBy(['post' => $post, 'user' => $this->getUser()]);
    
        if (!$existingLike) {
            // If the user hasn't liked the post yet, create a new Like entity
            $like = new Like();
            $like->setPost($post);
            $like->setUser($this->getUser());
    
            // Increment the number of likes
            $post->setNumLikes($post->getNumLikes() + 1);
    
            // Persist the changes to the database
            $entityManager->persist($like);
            $entityManager->flush();
        }
    
        // You can redirect to the post or any other page as needed
        return $this->redirectToRoute('blog'); // Adjust the route as needed
    }
    
    

    #[Route("/blog", name: 'blog')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Post::class);
        $posts = $repository->findAll();
        $recents = $repository->findRecents();
        
        return $this->render('blog/blog.html.twig', [
            'posts' => $posts,
            'recents' => $recents,
        ]);
    }

    #[Route("/single_post/{slug}", name: 'single_post')]
    public function post(ManagerRegistry $doctrine, Request $request, $slug = 'cambiar'): Response
    {
        return new Response("Single post");
    }
}
