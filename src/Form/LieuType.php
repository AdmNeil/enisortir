<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Lieu',
                'attr' => ['class' => "groupe"]
            ])
            ->add('rue', TextType::class, [
                'attr' => ['list' => 'adresseList', 'class' => "groupe"]
            ])
//            ->add('ville', VilleType::class)
            ->add('latitude', null, [
                'attr' => ['class' => "groupe"]
            ])
            ->add('longitude', null, [
                'attr' => ['class' => "groupe"]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
