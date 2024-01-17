<?php

namespace App\Controller\Admin;

use App\Entity\BookCategory;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BookCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BookCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Book Category')
            ->setEntityLabelInPlural('Book Categories');
    }

    public function configureFields(string $pageName): iterable
    {
        $entity = $this->getContext()->getEntity()->getInstance();

        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title');
        yield AssociationField::new('parentCategory')->setQueryBuilder(
            function (QueryBuilder $qb) use ($pageName, $entity) {
                $qb->orderBy('entity.title')
                    ->andWhere('entity.parentCategory IS NULL');

                if ($pageName === Crud::PAGE_EDIT) {
                    $qb->andWhere('entity != :entity')
                        ->setParameter('entity', $entity);
                    }
                }
        );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(
            Crud::PAGE_INDEX,
            Action::new('viewOnSite')->linkToUrl(
                function (BookCategory $bookCategory) {
                    return $this->generateUrl('book-category', [
                        'id' => $bookCategory->getId(),
                    ]);
                },
            ),
        );
    }
}
