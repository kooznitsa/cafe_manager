<?php

namespace App\Form\Type\User;

use App\Entity\Dish;
use App\Enum\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType, EnumType, HiddenType};
use Symfony\Component\Form\FormBuilderInterface;

class LinkedOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'dish',
            EntityType::class,
            ['class' => Dish::class, 'choice_label' => 'name', 'label' => 'Блюдо'],
        )
            ->add('status', EnumType::class, ['class' => Status::class])
            ->add('isDelivery', CheckboxType::class, ['required' => false])
            ->add('id', HiddenType::class);
    }
}
