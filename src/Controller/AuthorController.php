<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    private AuthorRepository $authorRepo;
    private EntityManagerInterface $entityManager;

    public function __construct(AuthorRepository $authorRepository, EntityManagerInterface $entityManager)
    {
        $this->authorRepo = $authorRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/showAuthor/{name}', name: 'app_showAuthor', defaults: ['name' => 'victor hugo'], methods: ['GET'])]
    public function showAuthor(string $name): Response
    {
        return $this->render('author/showAuthor.html.twig', [
            'name' => $name,
        ]);
    }

    #[Route('/authorList', name: 'app_authorList', methods: ['GET'])]
    public function authorList(): Response
    {
        $authors = $this->authorRepo->findAll();

        return $this->render('author/authorList.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/details/{id}', name: 'author_details', methods: ['GET'])]
    public function authorDetails(int $id): Response
    {
        $author = $this->authorRepo->find($id);

        if (!$author) {
            throw $this->createNotFoundException("Author with ID $id not found.");
        }

        return $this->render('author/details.html.twig', [
            'author' => $author,
        ]);
    }

    #[Route('/author/new', name: 'author_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($author);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_authorList');
        }

        return $this->render('author/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/delete/{id}', name: 'app_deleteAuthor', methods: ['GET'])]
    public function deleteAuthor(int $id): Response
    {
        $author = $this->authorRepo->find($id);

        if ($author) {
            $this->entityManager->remove($author);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_authorList');
    }
    #[Route('/update/{id}', name: 'app_updateAuthor', methods: ['GET','POST'])]
    public function updateAuthor(Request $request, Author $author): Response
    {
      $form = $this->createForm(AuthorType::class, $author);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
          $this->entityManager->flush();
          return $this->redirectToRoute('app_authorList');
      }
      return $this->render('author/update.html.twig', ['form' => $form->createView(), 'author' => $author]);
    }

}
