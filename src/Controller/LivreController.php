<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/livre')]
class LivreController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Display all books with search functionality
    #[Route('/', name: 'livre_index', methods: ['GET', 'POST'])]
    public function index(Request $request, LivreRepository $livreRepository): Response
    {
        // Create search form
        $searchForm = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('author', SearchType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Search by author...'],
                'label' => false,
            ])
            ->getForm();

        $searchForm->handleRequest($request);
        $livres = $livreRepository->findAll();

        // Filter books by author name if search is submitted
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $authorName = $searchForm->get('author')->getData();
            $livres = $livreRepository->findByAuthorName($authorName);
        }

        return $this->render('livre/index.html.twig', [
            'livres' => $livres,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    // Add a new book
    #[Route('/new', name: 'livre_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($livre);
            $this->entityManager->flush();

            return $this->redirectToRoute('livre_index');
        }

        return $this->render('livre/new.html.twig', ['form' => $form->createView()]);
    }

    // Edit an existing book
    #[Route('/{id}/edit', name: 'livre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livre $livre): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('livre_index');
        }

        return $this->render('livre/edit.html.twig', ['form' => $form->createView(), 'livre' => $livre]);
    }

    // Delete a book
    #[Route('/delete/{id}', name: 'livre_delete', methods: ['POST'])]
    public function delete(Request $request, Livre $livre): Response
    {
        if ($this->isCsrfTokenValid('delete' . $livre->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($livre);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('livre_index');
    }

    // Display book details
    #[Route('/{id}', name: 'livre_show', methods: ['GET'])]
    public function show(Livre $livre): Response
    {
        return $this->render('livre/show.html.twig', ['livre' => $livre]);
    }
}
