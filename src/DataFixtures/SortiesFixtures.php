<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class SortiesFixtures extends Fixture implements DependentFixtureInterface {
    
    ////////// options \\\\\\\\\\\\\
    private const                 NB_MIN_A_GENERER = 100;
    private const                 DATE_MIN         = '-120 days';
    private const                 DATE_MAX         = '+30 days';
    private const                 NB_PLACES_MIN    = 1;
    private const                 NB_PLACES_MAX    = 12;
    ////////////////////////////////
    
    public const REF_PREFIX = Sortie::class . '_';
    
    
    public static int $count = 0;
    
    public function load(ObjectManager $manager): void {
        $faker = Factory::create('fr_FR');
        
        while(self::$count++ < self::NB_MIN_A_GENERER) {
            $sortie = new Sortie();
            
            /** @var Lieu $lieu */
            $lieu = $this->getReference(
                LieuxFixtures::REF_PREFIX .
                random_int(0, LieuxFixtures::$count - 1)
            );
            
            /** @var Campus $campus */
            $campus = $this->getReference(
                CampusFixtures::REF_PREFIX .
                random_int(0, CampusFixtures::$count - 1)
            );
            
            /** @var Participant $organisateur */
            $organisateur = $this->getReference(
                ParticipantsFixtures::REF_PREFIX .
                random_int(0, ParticipantsFixtures::$count - 1)
            );
            
            $dateInscription = $faker->dateTimeBetween(self::DATE_MIN, self::DATE_MAX);
            $dateDebut       = $faker->dateTimeBetween($dateInscription, self::DATE_MAX);
            
            $sortie
                ->setLieu($lieu)
                ->setCampus($campus)
                ->setOrganisateur($organisateur)
                ->setNom($faker->unique()->realTextBetween(5, 20))
                ->setDateHeureDebut($dateDebut)
                ->setDuree($faker->numberBetween(1, 16) * 15)
                ->setDateLimiteInscription($dateInscription)
                ->setNbInscriptionsMax($faker->numberBetween(self::NB_PLACES_MIN, self::NB_PLACES_MAX))
                ->setInfosSortie($faker->realTextBetween(20, 600))
            ;
            
            // inscription d'un nombre aléatoire de participants
            $nbParticipants            = 0;
            $nbParticipantsMaxPossible = min(ParticipantsFixtures::$count - 1, $sortie->getNbInscriptionsMax());
            
            switch(random_int(0, 10)) {
                case 0:
                case 1:
                case 2:
                    $nbParticipants = 0;
                    break;
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                    $nbParticipants = random_int(0, $nbParticipantsMaxPossible);
                    break;
                case 8:
                case 9:
                case 10:
                    $nbParticipants = $nbParticipantsMaxPossible;
            }
            
            for(
                $participantIndex = 0;
                $participantIndex < $nbParticipants;
                $participantIndex++
            ) {
                /** @var Participant $participant */
                $participant = $this->getReference(ParticipantsFixtures::REF_PREFIX . $participantIndex);
                $sortie->addParticipant($participant);
            }
            
            // sélection de l'état
            /** @var Etat $etat */
            $etatsPossibles = [
                Etat::LABEL_CREEE,
                Etat::LABEL_OUVERTE,
                Etat::LABEL_CLOTUREE,
                Etat::LABEL_ANNULEE,
                Etat::LABEL_EN_COURS,
                Etat::LABEL_PASSEE,
                Etat::LABEL_HISTORISEE,
            ];
            
            // États possibles en fonction du nombre d'inscrits
            $etatsPossiblesNbParticipants = [];
            
            if($nbParticipants === 0)
                $etatsPossiblesNbParticipants = [
                    Etat::LABEL_CREEE,
                    Etat::LABEL_OUVERTE,
                    Etat::LABEL_ANNULEE,
                ];
            
            else if($nbParticipants >= $sortie->getNbInscriptionsMax())
                $etatsPossiblesNbParticipants = [
                    Etat::LABEL_CLOTUREE,
                    Etat::LABEL_ANNULEE,
                    Etat::LABEL_EN_COURS,
                    Etat::LABEL_PASSEE,
                    Etat::LABEL_HISTORISEE,
                ];
            
            else
                $etatsPossiblesNbParticipants = [
                    Etat::LABEL_OUVERTE,
                    Etat::LABEL_CLOTUREE,
                    Etat::LABEL_ANNULEE,
                    Etat::LABEL_EN_COURS,
                    Etat::LABEL_PASSEE,
                    Etat::LABEL_HISTORISEE,
                ];
            
            $etatsPossibles = array_intersect($etatsPossibles, $etatsPossiblesNbParticipants);
            
            
            // États possibles en fonction de la date
            $etatsPossiblesDates = [];
            $now                 = $faker->dateTime('now');
            
            if($sortie->getDateLimiteInscription() > $now)
                $etatsPossiblesDates = [
                    Etat::LABEL_CREEE,
                    Etat::LABEL_OUVERTE,
                    Etat::LABEL_CLOTUREE,
                    Etat::LABEL_ANNULEE,
                ];
            
            else if($sortie->getDateHeureDebut() > $now)
                $etatsPossiblesDates = [
                    Etat::LABEL_CLOTUREE,
                    Etat::LABEL_ANNULEE,
                ];
            
            else
                $etatsPossiblesDates = [
                    Etat::LABEL_EN_COURS,
                    Etat::LABEL_PASSEE,
                    Etat::LABEL_ANNULEE,
                    Etat::LABEL_HISTORISEE,
                ];
            
            $etatsPossibles = array_intersect($etatsPossibles, $etatsPossiblesDates);
            
            $choixEtat = array_rand($etatsPossibles);
            $etat      = $this->getReference($etatsPossibles[$choixEtat]);
            
            $sortie->setEtat($etat);
            
            $manager->persist($sortie);
        }
        
        $manager->flush();
    }
    
    public function getDependencies() {
        return [
            VillesFixtures::class,
            LieuxFixtures::class,
            EtatFixtures::class,
            AppFixtures::class,
        ];
    }
}
