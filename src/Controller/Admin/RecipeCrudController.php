<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, IdField, IntegerField};

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Рецепт')
            ->setEntityLabelInPlural('Рецепты')
            ->setSearchFields(['dish', 'product'])
            ->setPaginatorPageSize(100)
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('dish')->setSortProperty('name');
        yield AssociationField::new('product')->setSortProperty('name');
        yield IntegerField::new('amount');
    }
}
