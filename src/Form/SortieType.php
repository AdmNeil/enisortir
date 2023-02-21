<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie'
            ])
            ->add('dateHeureDeb', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text'
            ])
            ->add('dateCloture', DateType::class, [
                'label' => 'Date de clôture des inscriptions',
                'widget' => 'single_text'
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => ['min' => 1, 'max' => 100, 'value' => 1]
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => ['value' => 90]
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos'
            ])
            ->add('site', TextType::class, [
                'label' => 'Ville organisatrice',
                'disabled' => true
            ])
//            ->add('lieu',LieuType::class)
            ->add('saveSortie', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
            ->add('publishSortie', SubmitType::class, [
                'label' => 'Publier la sortie'
            ])
            ->add('removeSortie', SubmitType::class, [
                'label' => 'Annuler la sortie'
            ])
            ->add('annuleSortie', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
