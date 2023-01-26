<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture {
    public const ETAT_CREEE    = 'Créée';
    public const ETAT_OUVERTE  = 'Ouverte';
    public const ETAT_CLOSE    = 'Clôturée';
    public const ETAT_EN_COURS = 'Activité en cours';
    public const ETAT_PASSEE   = 'passée';
    public const ETAT_ANNULEE  = 'Annulée';
    
    private const ETATS = [
        self::ETAT_CREEE    => null,
        self::ETAT_OUVERTE  => null,
        self::ETAT_CLOSE    => null,
        self::ETAT_EN_COURS => null,
        self::ETAT_PASSEE   => null,
        self::ETAT_ANNULEE  => null,
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
