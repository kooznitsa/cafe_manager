<?php

namespace App\Controller\Admin;

use App\Entity\Dish;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, IdField, ImageField, MoneyField, TextField};

class DishCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Dish::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('category')->setSortProperty('name');
        yield TextField::new('name');
        yield MoneyField::new('price')
            ->setCurrency('RUB')
            ->setStoredAsCents(false)
            ->setNumDecimals(2);
        yield ImageField::new('image')
            ->setBasePath('uploads/images/')
            ->setUploadDir('assets/images/');
    }
}
