<?php

namespace App\Controller;

use App\Repository\BookCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private BookCategoryRepository $bookCategoryRepository
    ) {}

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $parentCategories = $this->bookCategoryRepository->findBy([
            'parentCategory' => null,
        ], ['title' => 'ASC']);

        return $this->render('index.html.twig', [
            'bookCategories' => $parentCategories,
        ]);
    }
}
