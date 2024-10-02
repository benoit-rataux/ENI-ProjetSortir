<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class VillesFixtures extends Fixture {
    
    ////////// options \\\\\\\\\\\\\
    private const  NB_MIN_A_GENERER = 12;
    ////////////////////////////////
    
    public const   REF_PREFIX = Ville::class . '_';
    public static int $count = 0;
    
    public function load(ObjectManager $manager): void {
        
        $faker = Factory::create('fr_FR');
        
        $ville = new Ville();
        $ville
            ->setNom('ISS')
            ->setCodePostal('H2G2')
        ;
        $manager->persist($ville);
        $this->addReference(self::REF_PREFIX . self::$count++, $ville);
        
        $this->generateRemaining($manager);
        
        $manager->flush();
    }
    
    private function generateRemaining(ObjectManager $manager) {
        $faker = Factory::create('fr_FR');
        
        while(self::$count < self::NB_MIN_A_GENERER) {
            $ville = new Ville();
            $ville
                ->setNom($faker->unique()->city())
                ->setCodePostal($faker->unique()->postcode())
            ;
            $manager->persist($ville);
            $this->addReference(self::REF_PREFIX . self::$count++, $ville);
        }
    }
}
