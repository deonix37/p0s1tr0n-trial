<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
