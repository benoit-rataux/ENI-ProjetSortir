<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture implements FixtureGroupInterface {
    
    public const ETATS_LABEL = [
        Etat::LABEL_CREEE,
        Etat::LABEL_OUVERTE,
        Etat::LABEL_CLOTUREE,
        Etat::LABEL_EN_COURS,
        Etat::LABEL_PASSEE,
        Etat::LABEL_ANNULEE,
        Etat::LABEL_HISTORISEE,
    ];
    
    public static function getGroups(): array {
        return ['etat', 'sortie'];
    }
    
    public function load(ObjectManager $manager): void {
        foreach(EtatFixtures::ETATS_LABEL as $label) {
            $etat = new Etat();
            $etat->setLibelle($label);
            $manager->persist($etat);
            $this->addReference($label, $etat);
        }
        
        $manager->flush();
    }
}
