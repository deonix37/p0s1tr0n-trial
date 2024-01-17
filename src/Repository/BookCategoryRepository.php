<?php

namespace App\Repository;

use App\Entity\BookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BookCategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private ParameterBagInterface $config
    )
    {
        parent::__construct($registry, BookCategory::class);
    }

    public function getOrCreateDefault(): BookCategory
    {
        $title = $this->config->get('app.book_parse_default_category');

        if ($category = $this->findOneBy(['title' => $title])) {
            return $category;
        }

        $category = new BookCategory();
        $category->setTitle($title);
        $this->getEntityManager()->persist($category);

        return $category;
    }
}
