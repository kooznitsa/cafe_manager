<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, BooleanField, DateTimeField, IdField, TextField};

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('user')->setSortProperty('name');
        yield AssociationField::new('dish')->setSortProperty('name');
        yield TextField::new('status');
        yield BooleanField::new('isDelivery');
        yield DateTimeField::new('created_at')->renderAsText()->hideOnForm();
        yield DateTimeField::new('updated_at')->renderAsText()->hideOnForm();
    }
}
