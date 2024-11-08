<?php

namespace App\Controller\Admin;

use App\Entity\Dish;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, BooleanField, IdField, ImageField, MoneyField, TextField};
use Symfony\Component\Validator\Constraints\Image;

class DishCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Dish::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Блюдо')
            ->setEntityLabelInPlural('Блюда')
            ->setSearchFields(['name'])
            ->setPaginatorPageSize(100)
            ->setDefaultSort(['isAvailable' => 'DESC'])
            ->setEntityPermission('ROLE_ADMIN')
            ;
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
        yield BooleanField::new('isAvailable');
    }
}
