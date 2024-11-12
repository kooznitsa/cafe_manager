<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{DateTimeField, IdField, IntegerField, TextField};

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Продукт')
            ->setEntityLabelInPlural('Продукты')
            ->setSearchFields(['name'])
            ->setPaginatorPageSize(100)
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield TextField::new('unit');
        yield IntegerField::new('amount')->hideOnForm();
        yield DateTimeField::new('updated_at')->renderAsText()->hideOnForm();
    }
}
