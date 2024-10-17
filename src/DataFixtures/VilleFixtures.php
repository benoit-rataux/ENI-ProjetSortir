<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture implements FixtureGroupInterface {
    
    use EntityGeneratorTrait;
    
    ////////// options \\\\\\\\\\\\\
    private const MIN_QUANTITY_TO_GENERATE = 5;
    
    ////////////////////////////////
    
    public static function getGroups(): array {
        return ['ville', 'lieu', 'sortie'];
    }
    
    public function load(ObjectManager $manager): void {
        $this->initialise($manager);
        
        $this->generateOne(
            'ISS',
            'H2G2',
        );
        
        $this->generateRemainings();
        
        $manager->flush();
    }
    
    private function generateOne(
        string $cityName = null,
        string $postCode = null,
    ): void {
        $cityName ??= $this->faker->unique()->city() . '-Bourg';
        $postCode ??= $this->faker->unique()->postCode();
        
        $ville = new Ville();
        $ville->setNom($cityName)
              ->setCodePostal($postCode)
        ;
        
        $this->save($ville);
    }
}
