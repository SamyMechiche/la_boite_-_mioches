<?php

namespace App\Form;

use App\Entity\Attendance;
use App\Entity\Child;
use App\Enum\HalfDay;
use App\Enum\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendanceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('child', EntityType::class, [
                'class' => Child::class,
                'choice_label' => function(Child $child) {
                    return $child->getFname() . ' ' . $child->getLname();
                },
                'placeholder' => 'Select your child',
                'required' => true,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date',
            ])
            ->add('half_day', ChoiceType::class, [
                'choices' => [
                    'Morning (9-12)' => HalfDay::morning,
                    'Afternoon (13-16)' => HalfDay::afternoon,
                    'Full Day (9-16)' => HalfDay::full_day,
                ],
                'required' => true,
                'label' => 'Time Period',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Present' => Status::PRESENT,
                    'Absent' => Status::ABSENT,
                    'Late' => Status::LATE,
                    'Justified Absence' => Status::JUSTIFIED,
                ],
                'required' => true,
                'label' => 'Status',
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Comments (optional)',
                'attr' => [
                    'placeholder' => 'Add any relevant comments about the attendance...',
                    'rows' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Attendance::class,
        ]);
    }
} 