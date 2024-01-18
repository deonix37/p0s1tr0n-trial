<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            TextField::new('isbn'),
            NumberField::new('pageCount'),
            AssociationField::new('status'),
            DateField::new('publishedDate'),
            ImageField::new('thumbnail')
                ->setBasePath(Book::THUMBNAIL_UPLOAD_PATH)
                ->setUploadDir('public' . Book::THUMBNAIL_UPLOAD_PATH),
            AssociationField::new('authors'),
            AssociationField::new('categories'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(
            Crud::PAGE_INDEX,
            Action::new('viewOnSite')->linkToUrl(
                function (Book $book) {
                    return $this->generateUrl('book', [
                        'id' => $book->getId(),
                    ]);
                },
            ),
        );
    }
}
