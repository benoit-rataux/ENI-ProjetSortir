<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class SortiesFixtures extends Fixture implements DependentFixtureInterface {
    public const RNG_SORTIES_COUNT_MIN = 0;
    public const RNG_SORTIES_COUNT_MAX = 8;
    
    public function load(ObjectManager $manager): void {
        $faker = Factory::create('fr_FR');
        
        $etats         = [
            $this->getReference(EtatFixtures::ETAT_CREEE),
            $this->getReference(EtatFixtures::ETAT_OUVERTE),
            $this->getReference(EtatFixtures::ETAT_CLOSE),
            $this->getReference(EtatFixtures::ETAT_EN_COURS),
        ];
        $etatsMaxIndex = count($etats) - 1;
        
        $campus         = [
            $this->getReference('campus_podlar'),
            $this->getReference('campus_ocean'),
            $this->getReference('campus_lespace'),
        ];
        $campusMaxIndex = count($campus) - 1;
        
        $organisateurs         = [
            $this->getReference('participant_admin'),
            $this->getReference('participant_Garry911'),
            $this->getReference('participant_La petite Sirene'),
            $this->getReference('participant_Spaaaaaace!'),
        ];
        $organisateursMaxIndex = count($organisateurs) - 1;
        
        for($i = 0; $i < VillesFixtures::RNG_VILLES_COUNT; $i++) {
            /** @var Ville $ville */
            $ville         = $this->getReference(VillesFixtures::RNG_VILLES . $i);
            $lieuxMaxIndex = count($ville->getLieux());
            
            for($j = 0; $j < random_int(self::RNG_SORTIES_COUNT_MIN, self::RNG_SORTIES_COUNT_MAX); $j++) {
                $sortie = new Sortie();
                $sortie
                    ->setEtat($etats[random_int(0, $etatsMaxIndex)])
                    ->setLieu($ville->getLieux()[random_int(0, $lieuxMaxIndex)])
                    ->setCampus($campus[random_int(0, $campusMaxIndex)])
                    ->setOrganisateur($organisateurs[random_int(0, $organisateursMaxIndex)])
                    ->setNom($faker->unique()->realTextBetween(1, 50))
                    ->setDateHeureDebut($faker->dateTimeBetween('now', '+30 days'))
                    ->setDuree($faker->numberBetween(1, 40) * 15)
                    ->setDateLimiteInscription($faker->dateTimeBetween('now', $sortie->getDateHeureDebut()))
                    ->setNbInscriptionsMax($faker->numberBetween(1, 50))
                    ->setInfosSortie($faker->realTextBetween(5, 600))
                ;
                $manager->persist($sortie);
            }
        }
        
        $manager->flush();
    }
    
    public function getDependencies() {
        return [
            VillesFixtures::class,
            EtatFixtures::class,
            LieuxFixtures::class,
            AppFixtures::class,
        ];
    }
}
