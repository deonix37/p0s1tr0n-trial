<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book/{id}', name: 'book')]
    public function show(
        Book $book,
        BookRepository $bookRepository
    ): Response
    {
        $relatedBooks = $bookRepository->getRelatedBooks($book, 9);

        return $this->render('book/show.html.twig', [
            'book' => $book,
            'relatedBooks' => $relatedBooks,
        ]);
    }
}
