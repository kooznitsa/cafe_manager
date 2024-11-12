<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{ArrayField, DateTimeField, IdField, TextField};

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Пользователь')
            ->setEntityLabelInPlural('Пользователи')
            ->setSearchFields(['email'])
            ->setPaginatorPageSize(100)
            ->setDefaultSort(['email' => 'ASC'])
            ->setEntityPermission('ROLE_ADMIN')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield TextField::new('email');
        yield TextField::new('password')->hideOnIndex();
        yield TextField::new('address');
        yield ArrayField::new('roles');
        yield DateTimeField::new('created_at')->renderAsText()->hideOnForm();
        yield DateTimeField::new('updated_at')->renderAsText()->hideOnForm();
    }
}
