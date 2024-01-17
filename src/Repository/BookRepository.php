<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\BookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private ParameterBagInterface $config
    )
    {
        parent::__construct($registry, Book::class);
    }

    public function createFromParsedData(
        array $data,
        array $existingStatuses,
        array $existingAuthors,
        array $existingCategories
    ): Book
    {
        $defaultCategoryTitle = $this->config->get('app.book_parse_default_category');
        $defaultCategory = $existingCategories[$defaultCategoryTitle];

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setIsbn($data['isbn']);
        $book->setPageCount($data['pageCount']);

        if (!empty($data['publishedDate'])) {
            $book->setPublishedDate(
                new \DateTimeImmutable($data['publishedDate']['$date'])
            );
        }

        if (!empty($data['thumbnailUrl'])) {
            $book->setThumbnail(
                md5($data['thumbnailUrl'])
                . '.'
                . pathinfo($data['thumbnailUrl'], PATHINFO_EXTENSION)
            );
        }

        if (isset($existingStatuses[$data['status']])) {
            $book->setStatus($existingStatuses[$data['status']]);
        }

        foreach ($data['authors'] as $dataAuthor) {
            if (isset($existingAuthors[$dataAuthor])) {
                $book->addAuthor($existingAuthors[$dataAuthor]);
            }
        }

        foreach ($data['categories'] as $dataCategory) {
            if (isset($existingCategories[$dataCategory])) {
                $book->addCategory($existingCategories[$dataCategory]);
            }
        }

        if (!$data['categories']) {
            $book->addCategory($defaultCategory);
        }

        return $book;
    }

    public function getPaginator(
        int $page = 1,
        ?string $title = null,
        ?string $authorName = null,
        ?int $statusId = null,
        ?BookCategory $category = null,
    ): Paginator
    {
        $pageSize = $this->config->get('app.book_paginator_page_size');

        $books = $this->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC');

        if ($category) {
            $books->andWhere(
                    ':category MEMBER OF b.categories
                    OR :subcategories MEMBER OF b.categories'
                )
                ->setParameter('category', $category)
                ->setParameters([
                    'category' => $category,
                    'subcategories' => $category->getChildCategories(),
                ]);
        }

        if ($title) {
            $books->andWhere('b.title LIKE :title')
                ->setParameter(
                    'title',
                    '%' . addcslashes($title, '%_') . '%'
                );
        }

        if ($authorName) {
            $books->join('b.authors', 'ba')
                ->andWhere('ba.name LIKE :authorName')
                ->setParameter(
                    'authorName',
                    '%' . addcslashes($authorName, '%_') . '%'
                );
        }

        if ($statusId) {
            $books->andWhere('b.status = :statusId')
                ->setParameter('statusId', $statusId);
        }

        $paginator = new Paginator($books);
        $paginator->getQuery()
            ->setFirstResult($pageSize * ($page - 1))
            ->setMaxResults($pageSize);

        return $paginator;
    }

    public function getRelatedBooks(Book $book, ?int $limit = null): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere(
                'b != :book',
                ':categories MEMBER OF b.categories'
            )
            ->setParameters([
                'book' => $book,
                'categories' => $book->getCategories(),
            ])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
