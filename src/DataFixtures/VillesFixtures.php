<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class VillesFixtures extends Fixture {
    public const  RNG_VILLES_COUNT = 12;
    public const  RNG_VILLES       = 'rngVille_';
    public const  VILLE_ISS        = 'ISS';
    
    public function load(ObjectManager $manager): void {
        
        $faker = Factory::create('fr_FR');
        
        $ville = new Ville();
        $ville
            ->setNom('ISS')
            ->setCodePostal('H2G2')
        ;
        $manager->persist($ville);
        $this->addReference(self::VILLE_ISS, $ville);
        
        // some random villes
        for($i = 0; $i < self::RNG_VILLES_COUNT; $i++) {
            $ville = new Ville();
            $ville
                ->setNom($faker->unique()->city)
                ->setCodePostal($faker->unique()->postcode)
            ;
            $manager->persist($ville);
            $this->addReference(self::RNG_VILLES . $i, $ville);
        }
        
        $manager->flush();
    }
}
