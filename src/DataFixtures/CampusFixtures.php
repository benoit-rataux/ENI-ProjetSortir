<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture implements FixtureGroupInterface {
    
    use EntityGeneratorTrait;
    
    private const MIN_QUANTITY_TO_GENERATE = 5;
    
    public static function getGroups(): array {
        return ['campus', 'participant', 'sortie'];
    }
    
    public function load(ObjectManager $manager): void {
        $this->initialise($manager);
        
        $this->generateOne('Peau-de-Lard');
        $this->generateOne('Soulloceiyan');
        $this->generateOne('L\'espace');
        
        $this->generateRemainings();
        
        $manager->flush();
    }
    
    private function generateOne(string $campusName = null): void {
        $campusName ??= 'Campus St ' . $this->faker->unique()->name();
        
        $campus = new Campus();
        $campus->setNom($campusName);
        
        $this->save($campus);
    }
}
