<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CampusFixtures extends Fixture {
    
    ////////// options \\\\\\\\\\\\\
    private const        NB_MIN_A_GENERER = 10;
    ////////////////////////////////
    
    public const REF_PREFIX = Campus::class . '_';
    public static $count = 0;
    
    public function load(ObjectManager $manager): void {
        $podlar = new Campus();
        $podlar->setNom('Peau-de-Lard');
        $manager->persist($podlar);
        $this->addReference(self::REF_PREFIX . self::$count++, $podlar);
        
        $ocean = new Campus();
        $ocean->setNom('Soulloceiyan!');
        $manager->persist($ocean);
        $this->addReference(self::REF_PREFIX . self::$count++, $ocean);
        
        $lespace = new Campus();
        $lespace->setNom('L\'espace');
        $manager->persist($lespace);
        $this->addReference(self::REF_PREFIX . self::$count++, $lespace);
        
        $this->generateRemaining($manager);
        
        $manager->flush();
    }
    
    private function generateRemaining(ObjectManager $manager) {
        $faker = Factory::create('fr_FR');
        
        while(self::$count > self::NB_MIN_A_GENERER) {
            $campus = new Campus();
            $campus->setNom('Campus St ' . $faker->unique()->name());
            $manager->persist($campus);
            $this->addReference(self::REF_PREFIX . self::$count++, $campus);
        }
    }
}
