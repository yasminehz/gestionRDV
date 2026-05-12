<?php

namespace App\Form;

use App\Entity\Indication;
use App\Entity\Medicament;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;

class IndicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Médicament : liste déroulante alimentée par la table medicament
            ->add('medicament', EntityType::class, [
                'class'        => Medicament::class,
                'choice_label' => 'libelle',
                'label'        => 'Médicament',
                'placeholder'  => '-- Choisir un médicament --',
            ])
            // Quantité par prise (ex : 1 comprimé, 2 gélules...)
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité par prise',
                'attr'  => ['min' => 1],
                // Validation : impossible de prescrire 0 ou un nombre négatif
                'constraints' => [new Positive(message: 'La quantité doit être strictement positive.')],
            ])
            // Durée du traitement, exprimée en jours
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en jours)',
                'attr'  => ['min' => 1],
                'constraints' => [new Positive(message: 'La durée doit être strictement positive.')],
            ])
            // Nombre de prises par jour (posologie)
            ->add('nbPriseParJour', IntegerType::class, [
                'label' => 'Nombre de prises par jour',
                'attr'  => ['min' => 1],
                'constraints' => [new Positive(message: 'Le nombre de prises doit être strictement positif.')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Indication::class,
        ]);
    }
}
