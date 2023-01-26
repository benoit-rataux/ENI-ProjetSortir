<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture {
    public const REF_PREFIX = 'Campus_';
    
    public function load(ObjectManager $manager): void {
        $podlar = new Campus();
        $podlar->setNom('Peau-de-Lard');
        $manager->persist($podlar);
        $this->addReference(self::REF_PREFIX . 'podlar', $podlar);
        
        $ocean = new Campus();
        $ocean->setNom('Soulloceiyan!');
        $manager->persist($ocean);
        $this->addReference(self::REF_PREFIX . 'ocean', $ocean);
        
        $lespace = new Campus();
        $lespace->setNom('L\'espace');
        $manager->persist($lespace);
        $this->addReference(self::REF_PREFIX . 'lespace', $lespace);
        
        $manager->flush();
    }
}
