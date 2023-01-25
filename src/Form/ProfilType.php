<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('pseudo', TextType::class, [
                'disabled' => true, //@TODO: vérifier l'unicité et autoriser le changement du pseudo
            ])
            ->add('prenom')
            ->add('nom')
            ->add('mail')
            ->add('telephone')
            ->add('campus', entityType::class, [
                'class'        => Campus::class,
                'choice_label' => 'nom',
                'expanded'     => false,
                'disabled'     => true,
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
