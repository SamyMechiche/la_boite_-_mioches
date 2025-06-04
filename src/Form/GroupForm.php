<?php

namespace App\Form;

use App\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('age_group', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => \App\Enum\AgeGroup::cases(),
                'choice_label' => fn($choice) => $choice->value,
                'choice_value' => fn($choice) => $choice?->value,
                'label' => 'Age Range',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}
