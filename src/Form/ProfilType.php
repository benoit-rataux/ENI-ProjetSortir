<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('pseudo', TextType::class, [
                'disabled' => false, //@TODO: demander au prof changement du pseudo
            ])
            ->add('prenom')
            ->add('nom')
            ->add('mail')
            ->add('telephone')
            ->add('motPasse', RepeatedType::class, [
                'type'            => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'required'        => true,
                'options'         => [
                    'attr' => ['class' => 'password-field']
                ],
                'first_options'   => [
                    'label' => 'Mot de passe',
                ],
                'second_options'  => [
                    'label' => 'confirmation',
                ],
            ])
            ->add('campus', entityType::class, [
                'class'        => Campus::class,
                'choice_label' => 'nom',
                'expanded'     => false,
                'disabled'     => true,
            ])/*
            ->add('sortiesEstInscrit')
            ->add('administrateur')
            ->add('actif')*/
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
