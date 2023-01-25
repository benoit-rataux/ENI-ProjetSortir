<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MotDePasseType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
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
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
