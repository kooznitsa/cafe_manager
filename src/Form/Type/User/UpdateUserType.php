<?php

namespace App\Form\Type\User;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class UpdateUserType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('orders', CollectionType::class, [
                'entry_type' => LinkedOrderType::class,
                'entry_options' => ['label' => false],
            ])
            ->setMethod('PATCH');
    }
}
