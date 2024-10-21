<?php

namespace App\Form\Type\User;

use App\DTO\ManageUserDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{EmailType, SubmitType, TextType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Имя',
                'attr' => [
                    'data-time' => time(),
                    'placeholder' => 'Имя пользователя',
                    'class' => 'user-name',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Эмейл',
                'attr' => [
                    'placeholder' => 'email@example.com',
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Адрес',
                'required' => false,
                'attr' => [
                    'data-time' => time(),
                    'placeholder' => 'Адрес пользователя',
                    'class' => 'user-address',
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Отправить']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ManageUserDTO::class,
            'empty_data' => new ManageUserDTO(),
            'isNew' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'save_user';
    }
}
