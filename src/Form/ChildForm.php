<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\ExternalContact;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ChildForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fname')
            ->add('lname')
            ->add('birth_date')
            ->add('picture')
            ->add('allergies')
            ->add('signing_date')
            ->add('relation_type')
            ->add('parent', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getFname() . ' ' . $user->getLname() . ' (' . $user->getEmail() . ')';
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_PARENT%');
                },
                'required' => true,
                'placeholder' => 'Select a parent',
                'label' => 'Parent'
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getFname() . ' ' . $user->getLname() . ' (' . $user->getEmail() . ')';
                },
                'multiple' => true,
                'required' => false,
                'label' => 'Additional Users'
            ])
            ->add('ExternalContact', EntityType::class, [
                'class' => ExternalContact::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'label' => 'External Contacts'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Child::class,
        ]);
    }
}
