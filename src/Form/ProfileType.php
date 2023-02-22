<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom', TextType::class, [
                'attr' => ['class' => "groupe"]
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => "groupe"]
            ])
            ->add('telephone', TextType::class, [
                'attr' => ['class' => "groupe"]
            ] )
            ->add('mail',EmailType::class, [
                'attr' => ['class' => "groupe"]
            ] )
            ->add('username', TextType::class, [
                'attr' => ['class' => "groupe"]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe que vous avez saisis ne correspondent pas. Veuillez saisir votre mot de passe dans le champ "Passeword" et confirmer votre saisie dans le champ "Repeat Passeword".',
                'required' => true,
                'trim' => true,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer mot de passe'],
                'attr' => ['class' => "groupe"]
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'attr' => ['class' => "groupe"]
            ])
            //->add('sortiesParticipant')
            ->add('imageFile', FileType::class, [
                'label' => 'Profile picture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new file (['mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/svg+xml'
                    ],
                        'mimeTypesMessage' => 'Merci de charger un fichier image valide (.jpeg, .png, .svg)',
                    ])
                ],
                'attr' => ['class' => "groupe"]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
