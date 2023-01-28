<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuxFixtures extends Fixture implements DependentFixtureInterface {
    
    ////////// options \\\\\\\\\\\\\
    private const RNG_LIEUX_PER_VILLE_MIN = 1;
    private const RNG_LIEUX_PER_VILLE_MAX = 10;
    ////////////////////////////////
    
    public const  REF_PREFIX = Lieu::class . '_';
    public static int $count = 0;
    
    public function load(ObjectManager $manager): void {
        
        $faker = Factory::create('fr_FR');
        
        /*************************/
        /** @var Ville $villeISS */
        $villeISS = $this->getReference(VillesFixtures::REF_PREFIX . '0');
        
        $lieu = new Lieu();
        $lieu
            ->setNom('module Russe')
            ->setRue('3ieme couloir à GAUCHE')
            ->setVille($villeISS)
        ;
        $manager->persist($lieu);
        
        $lieu = new Lieu();
        $lieu
            ->setNom('toilettes')
            ->setRue('gauche-gauche-haut-droite après le module Russe')
            ->setVille($villeISS)
        ;
        $manager->persist($lieu);
        
        /*************************/
        
        for($villeIndex = 0; $villeIndex < VillesFixtures::$count; $villeIndex++) {
            /** @var Ville $ville */
            $ville = $this->getReference(VillesFixtures::REF_PREFIX . $villeIndex);
            
            // nombre de lieux par villes
            $lieuxCount = random_int(self::RNG_LIEUX_PER_VILLE_MIN, self::RNG_LIEUX_PER_VILLE_MAX);
            
            for($lieuIndex = 0; $lieuIndex < $lieuxCount; $lieuIndex++) {
                $wordsCount = random_int(1, 5);
                
                $lieu = (new Lieu())
                    ->setNom($faker->unique()->words($wordsCount, true))
                    ->setRue($faker->streetName())
                    ->setVille($ville)
                ;
                $manager->persist($lieu);
                $this->addReference(self::REF_PREFIX . self::$count++, $lieu);
            }
        }
        
        $manager->flush();
    }
    
    public function getDependencies() {
        return [
            VillesFixtures::class,
        ];
    }
}
