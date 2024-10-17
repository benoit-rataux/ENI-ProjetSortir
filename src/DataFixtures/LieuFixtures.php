<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    
    use EntityGeneratorTrait;
    
    ////////// options \\\\\\\\\\\\\
    private const MIN_QUANTITY_TO_GENERATE = 20;
    private const RNG_LIEUX_PER_VILLE_MIN  = 1;
    private const RNG_LIEUX_PER_VILLE_MAX  = 10;
    
    ////////////////////////////////
    
    public static function getGroups(): array {
        return ['lieu', 'sortie'];
    }
    
    public function load(ObjectManager $manager): void {
        $this->initialise($manager);
        
        /** @var Ville $villeISS */
        $villeISS = VilleFixtures::getOne($this, 1);
        
        $this->generateOne(
            $villeISS,
            'module Russe',
            '3ieme couloir à GAUCHE',
        );
        
        $this->generateOne(
            $villeISS,
            'Canadarm II',
            'Probablement accroché à quekpart...',
        );
        
        $this->generateOne(
            $villeISS,
            'toilettes',
            'gauche-gauche-haut-droite après le module Russe',
        );
        
        $this->generateRemainings();
        
        $manager->flush();
    }
    
    public function getDependencies(): array {
        return [
            VilleFixtures::class,
        ];
    }
    
    private function generateOne(
        Ville  $ville = null,
        string $name = null,
        string $street = null,
        string $latitude = null,
        string $longitude = null,
    ): void {
        /** @var Ville $ville */
        $ville     ??= VilleFixtures::getOne($this);
        $name      ??= $this->faker->unique()->words(random_int(1, 5), true);
        $street    ??= $this->faker->streetName();
        $latitude  ??= $this->faker->latitude();
        $longitude ??= $this->faker->longitude();
        
        $lieu = new Lieu();
        $lieu->setVille($ville)
             ->setNom($name)
             ->setRue($street)
             ->setLatitude($latitude)
             ->setLongitude($longitude)
        ;
        
        $this->save($lieu);
    }
}
