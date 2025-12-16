<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('debut', DateType::class, [
                    'widget' => 'single_text',
                    'html5' => false,
                    'input' => 'datetime',
                    'attr' => ['class' => 'js-datepicker'],
                ])
                ->add('creneau', ChoiceType::class, [
                    'mapped' => false,
                    'choices' => [
                        '09:00' => '09:00', '09:30' => '09:30',
                        '10:00' => '10:00', '10:30' => '10:30',
                        '11:00' => '11:00', '11:30' => '11:30',
                        '13:00' => '13:00', '13:30' => '13:30',
                        '14:00' => '14:00', '14:30' => '14:30',
                        '15:00' => '15:00', '15:30' => '15:30',
                        '16:00' => '16:00', '16:30' => '16:30',
                    ],
                    'attr' => ['class' => 'js-creneau'],
                ])
            // patient is assigned from the authenticated user in the controller
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => function(Medecin $medecin) {
                    return $medecin->getPrenom() . ' ' . $medecin->getNom();
                },
            ])
            // 'etat' is set automatically in the controller (default id = 1)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
