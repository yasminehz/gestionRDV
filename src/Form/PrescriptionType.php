<?php

namespace App\Form;

use App\Entity\Medicament;
use App\Entity\Prescription;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite')
            ->add('nombrePrise')
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en jours)',
                'attr' => ['placeholder' => 'Nombre de jours'],
            ]);

        if ($options['include_rendez_vous']) {
            $builder->add('RendezVous', EntityType::class, [
                'class' => RendezVous::class,
                'choice_label' => 'libelle',
            ]);
        }

        $builder->add('medicament', EntityType::class, [
            'class' => Medicament::class,
            'choice_label' => 'libelle',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
            'include_rendez_vous' => true,
        ]);
    }
}
