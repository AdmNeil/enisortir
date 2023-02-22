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
                'label' => 'Nom de la sortie',
                'attr' => ['class' => "groupe"]
            ])
            ->add('dateHeureDeb', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'attr' => ['class' => "groupe"]
            ])
            ->add('dateCloture', DateType::class, [
                'label' => 'Date de clôture des inscriptions',
                'widget' => 'single_text',
                'attr' => ['class' => "groupe"]
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => ['min' => 1, 'max' => 100, 'value' => 1, 'class' => "groupe"]
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => ['value' => 90, 'class' => "groupe"]
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
                'attr' => ['class' => "groupe"]
            ])
            ->add('site', TextType::class, [
                'label' => 'Ville organisatrice',
                'disabled' => true,
                'attr' => ['class' => "groupe"]
            ])
//            ->add('lieu',LieuType::class)
            ->add('saveSortie', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => "button"]
            ])
            ->add('publishSortie', SubmitType::class, [
                'label' => 'Publier la sortie',
                'attr' => ['class' => "button"]
            ])
            ->add('removeSortie', SubmitType::class, [
                'label' => 'Supprimer la sortie'
                'attr' => ['class' => "button"]
            ])
            ->add('annuleSortie', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => "button"]
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
