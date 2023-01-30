<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture {
    
    private const ETATS = [
        Etat::LABEL_CREEE      => null,
        Etat::LABEL_OUVERTE    => null,
        Etat::LABEL_CLOTUREE   => null,
        Etat::LABEL_EN_COURS   => null,
        Etat::LABEL_PASSEE     => null,
        Etat::LABEL_ANNULEE    => null,
        Etat::LABEL_HISTORISEE => null,
    ];
    
    public function load(ObjectManager $manager): void {
        foreach(self::ETATS as $key => $value) {
            $etat = new Etat();
            $etat->setLibelle($key);
            $manager->persist($etat);
            $this->addReference($key, $etat);
        }
        
        $manager->flush();
    }
}
