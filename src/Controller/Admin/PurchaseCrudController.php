<?php

namespace App\Controller\Admin;

use App\Entity\Purchase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, DateTimeField, IdField, MoneyField, NumberField};

class PurchaseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Purchase::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Закупку')
            ->setEntityLabelInPlural('Закупки')
            ->setSearchFields(['product'])
            ->setPaginatorPageSize(100)
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('product')->setSortProperty('name');
        yield MoneyField::new('price')
            ->setCurrency('RUB')
            ->setStoredAsCents(false)
            ->setNumDecimals(2);
        yield NumberField::new('amount');
        yield DateTimeField::new('created_at')->renderAsText()->hideOnForm();
        yield DateTimeField::new('updated_at')->renderAsText()->hideOnForm();
    }
}
