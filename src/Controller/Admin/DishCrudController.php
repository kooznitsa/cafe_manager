<?php

namespace App\Controller\Admin;

use App\Entity\Dish;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, IdField, ImageField, MoneyField, TextField};
use Symfony\Component\Validator\Constraints\Image;

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
            ->setUploadDir('public/uploads/images/')
            ->setFileConstraints(new Image(
                maxSize: '1000k',
                minWidth: 1920,
                maxWidth: 1921,
                maxHeight: 1281,
                minHeight: 1280,
                allowSquare: false,
                allowPortrait: false,
            ));
    }
}
