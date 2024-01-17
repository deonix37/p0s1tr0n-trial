<?php

namespace App\Controller;

use App\Entity\BookCategory;
use App\Repository\BookCategoryRepository;
use App\Repository\BookRepository;
use App\Repository\BookStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class BookCategoryController extends AbstractController
{
    #[Route('/book-category/{id}', name: 'book-category')]
    public function show(
        BookCategory $category,
        BookCategoryRepository $bookCategoryRepository,
        BookRepository $bookRepository,
        BookStatusRepository $bookStatusRepository,
        #[MapQueryParameter] ?int $page = 1,
        #[MapQueryParameter] ?string $title = null,
        #[MapQueryParameter] ?string $author_name = null,
        #[MapQueryParameter(flags: FILTER_NULL_ON_FAILURE)] ?int $status_id = null,
    ): Response
    {
        $booksPaginator = $bookRepository->getPaginator(
            $page,
            $title,
            $author_name,
            $status_id,
            $category
        );
        $subcategories = $bookCategoryRepository->findBy([
            'parentCategory' => $category,
        ]);
        $bookStatuses = $bookStatusRepository->findAll();

        return $this->render('book-category/show.html.twig', [
            'category' => $category,
            'subcategories' => $subcategories,
            'bookStatuses' => $bookStatuses,
            'booksPaginator' => $booksPaginator,
        ]);
    }
}
