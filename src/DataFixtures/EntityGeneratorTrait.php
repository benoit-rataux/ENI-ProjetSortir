<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

trait EntityGeneratorTrait {
    
    private static string $referenceLabel;
    private static string $entityName;
    private static int $count = 0;
    private ObjectManager $objectManager;
    private Generator $faker;
    
    public static function getEntityName() {
        static::$entityName ??= str_replace('Fixtures',
                                            '',
                                            (new \ReflectionClass(get_called_class()))->getShortName());
        return static::$entityName;
    }
    
    public static function getReferencePrefix(): string {
        static::$referenceLabel ??= static::getEntityName() . '_';
        return static::$referenceLabel;
    }
    
    public static function countSaved(): int {
        return static::$count;
    }
    
    public static function getOne(Fixture $fixture,
                                  int     $index = null) {
        $index ??= random_int(1, static::countSaved());
        
        $reference = static::getReferencePrefix() . $index;
//        dump('-> get one ' .
//             ' of ' .
//             static::countSaved() .
//             ' ' .
//             static::getEntityName() .
//             ' : ' .
//             $reference);
        return $fixture->getReference($reference);
    }
    
    private function save(object $entity): void {
        static::$count++;
        $reference = $this->getReferencePrefix() . $this->countSaved();

//      dump('save ' . $reference);
        $this->setReference($reference, $entity);
        $this->objectManager->persist($entity);
    }
    
    abstract private function generateOne(): void;
    
    private function generateRemainings(): void {
        while($this->countSaved() < static::MIN_QUANTITY_TO_GENERATE) {
            $this->generateOne();
        }
    }
    
    private function initialise(ObjectManager $manager): void {
        $this->objectManager = $manager;
        $this->faker         = Factory::create('fr_FR');
    }
}