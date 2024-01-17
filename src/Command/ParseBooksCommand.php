<?php

namespace App\Command;

use App\Entity\BookAuthor;
use App\Entity\BookCategory;
use App\Entity\BookStatus;
use App\Repository\BookAuthorRepository;
use App\Repository\BookCategoryRepository;
use App\Repository\BookRepository;
use App\Repository\BookStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:parse-books')]
class ParseBooksCommand extends Command
{
    private array $booksData;
    private array $thumbnailsData;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private BookCategoryRepository $bookCategoryRepository,
        private BookRepository $bookRepository,
        private BookStatusRepository $bookStatusRepository,
        private BookAuthorRepository $bookAuthorRepository,
        private ParameterBagInterface $config,
        private HttpClientInterface $http
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setBooksData();
        $this->createStatuses();
        $this->createAuthors();
        $this->createCategories();
        $this->createBooks();
        $this->downloadBooksThumbnails();
        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function setBooksData(): void
    {
        $bookParseUrl = $this->config->get('app.book_parse_url');

        $this->booksData = $this->http->request('GET', $bookParseUrl)->toArray();

        # Make titles consistent
        foreach ($this->booksData as &$bookData) {
            foreach (['categories', 'authors'] as $array) {
                $bookData[$array] = array_map(function ($title) {
                    return mb_convert_case($title, MB_CASE_TITLE);
                }, $bookData[$array]);
            }
        }
    }

    private function createStatuses(): void
    {
        $existingStatuses = $this->getExistingStatuses();

        foreach ($this->booksData as $bookData) {
            if (
                empty($bookData['status'])
                || isset($existingStatuses[$bookData['status']])
            ) {
                continue;
            }

            $existingStatuses[$bookData['status']] = true;

            $status = new BookStatus();
            $status->setTitle($bookData['status']);

            $this->entityManager->persist($status);
        }

        $this->entityManager->flush();
    }

    private function createAuthors(): void
    {
        $existingAuthors = $this->getExistingAuthors();

        foreach ($this->booksData as $bookData) {
            foreach ($bookData['authors'] as $dataAuthor) {
                if (
                    empty($dataAuthor)
                    || isset($existingAuthors[$dataAuthor])
                ) {
                    continue;
                }

                $existingAuthors[$dataAuthor] = true;

                $author = new BookAuthor();
                $author->setName($dataAuthor);

                $this->entityManager->persist($author);
            }
        }

        $this->entityManager->flush();
    }

    private function createCategories(): void
    {
        $this->bookCategoryRepository->getOrCreateDefault();

        $existingCategories = $this->getExistingCategories();

        foreach ($this->booksData as $bookData) {
            foreach ($bookData['categories'] as $dataCategory) {
                if (
                    empty($dataCategory)
                    || isset($existingCategories[$dataCategory])
                ) {
                    continue;
                }

                $existingCategories[$dataCategory] = true;

                $category = new BookCategory();
                $category->setTitle($dataCategory);

                $this->entityManager->persist($category);
            }
        }

        $this->entityManager->flush();
    }

    private function createBooks(): void
    {
        $existingBooks = $this->getExistingBooks();
        $existingStatuses = $this->getExistingStatuses();
        $existingAuthors = $this->getExistingAuthors();
        $existingCategories = $this->getExistingCategories();

        foreach ($this->booksData as $bookData) {
            if (
                empty($bookData['isbn'])
                || isset($existingBooks[$bookData['isbn']])
            ) {
                continue;
            }

            $existingBooks[$bookData['isbn']] = true;

            $book = $this->bookRepository->createFromParsedData(
                $bookData,
                $existingStatuses,
                $existingAuthors,
                $existingCategories
            );

            if (!empty($data['thumbnailUrl'])) {
                $this->thumbnailsData[] = [
                    'book' => $book,
                    'response' => $this->http->request(
                        'GET',
                        $bookData['thumbnailUrl']
                    )
                ];
            }

            $this->entityManager->persist($book);
        }
    }

    private function downloadBooksThumbnails(): void
    {
        foreach ($this->thumbnailsData as $thumbnailData) {
            ['book' => $book, 'response' => $response] = $thumbnailData;

            try {
                $responseContent = $response->getContent();
            } catch (ExceptionInterface) {
                $responseContent = null;
            }

            if ($responseContent) {
                file_put_contents(
                    "public{$book->getThumbnailPath()}",
                    $responseContent
                );
            } else {
                $book->setThumbnail(null);
            }
        }
    }

    private function getExistingBooks(): array
    {
        return $this->bookRepository
            ->createQueryBuilder('b', 'b.isbn')
            ->select('b.isbn')
            ->getQuery()
            ->getResult();
    }

    private function getExistingCategories(): array
    {
        return $this->bookCategoryRepository
            ->createQueryBuilder('bc', 'bc.title')
            ->getQuery()
            ->getResult();
    }

    private function getExistingStatuses(): array
    {
        return $this->bookStatusRepository
            ->createQueryBuilder('bs', 'bs.title')
            ->getQuery()
            ->getResult();
    }

    private function getExistingAuthors(): array
    {
        return $this->bookAuthorRepository
            ->createQueryBuilder('ba', 'ba.name')
            ->getQuery()
            ->getResult();
    }
}
