<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Group;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Password (leave empty to keep current)'
            ])
            ->add('fname')
            ->add('lname')
            ->add('signing_date')
            ->add('phone')
            ->add('annual_income')
            ->add('isVerified')
            ->add('Child', EntityType::class, [
                'class' => Child::class,
                'choice_label' => 'fname',
                'multiple' => true,
            ])
            ->add('GroupId', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
