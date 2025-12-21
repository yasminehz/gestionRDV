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
