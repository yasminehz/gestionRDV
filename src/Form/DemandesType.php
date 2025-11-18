<?php

namespace App\Form;

use App\Entity\Demandes;
use App\Entity\Medecin;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datetime')
            ->add('etat')
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => 'id',
            ])
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demandes::class,
        ]);
    }
}
