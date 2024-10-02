<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use DateTimeInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class SortiesFixtures extends Fixture implements DependentFixtureInterface {
    
    ////////// options \\\\\\\\\\\\\
    private const                 NB_MIN_A_GENERER      = 100;
    private const                 DATE_MIN              = '-120 days';
    private const                 DATE_MAX              = '+30 days';
    private const                 NB_PLACES_MIN         = 1;
    private const                 NB_PLACES_MAX         = 12;
    private const                 CHANCES_INSCRITS_ZERO = 0.3;    // float entre 0 et 1
    private const                 CHANCES_INSCRITS_MAX  = 0.3;    // float entre 0 et 1
    ////////////////////////////////
    
    public const REF_PREFIX = Sortie::class . '_';
    
    
    public static int $count = 0;
    
    public function load(ObjectManager $manager): void {
        $this->genererSortie(
            $manager,
            'super',
        );
        
        $this->generateRemaining($manager);
        
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
    
    private function generateRemaining(ObjectManager $manager) {
        while(self::$count++ < self::NB_MIN_A_GENERER) {
            $this->genererSortie($manager);
        }
    }
    
    private function inscrireParticipants(
        Sortie $sortie,
        array  $participants = null,
        int    $nbParticipants = null,
        float  $chancesZeroParticipants = 0.3,
        float  $chancesMaxParticipants = 0.3,
    ): Sortie {
        
        if($participants === null) {
            // inscription d'un nombre aléatoire de participants
            if($nbParticipants === null) {
                $nbParticipantsMaxPossible = min(ParticipantsFixtures::$count - 1, $sortie->getNbInscriptionsMax());
                
                $roll = random_int(1, 100);
                if($roll <= $chancesZeroParticipants)
                    $nbParticipants = 0;
                else if($roll >= (1 - $chancesMaxParticipants))
                    $nbParticipants = min(ParticipantsFixtures::$count - 1, $sortie->getNbInscriptionsMax());
                else
                    $nbParticipants = random_int(0, $nbParticipantsMaxPossible);
            }
            
            $nbInscrits = 0;
            while($nbInscrits < $nbParticipants) {
                /** @var Participant $participant */
                $participantIndex = random_int(0, ParticipantsFixtures::$count);
                $participant      = $this->getReference(ParticipantsFixtures::REF_PREFIX . $participantIndex);
                
                if($participants->has($participant))
                    break;
                
                $participants[] = $participant;
                $nbInscrits++;
            }
        }
        
        foreach(array_unique($participants) as $participant) {
            if($participant instanceof Participant)
                $sortie->addParticipant($participant);
        }
        
        return $sortie;
    }
    
    private function filtreEtatsNbParticipants(Sortie $sortie, $etatsPossibles): array {
        $etatsPossiblesNbParticipants = [];
        
        $nbParticipants = count($sortie->getParticipants());
        
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
        
        return $etatsPossibles;
    }
    
    private function filtreEtatsDates(Sortie $sortie, array $etatsPossibles) {
        $etatsPossiblesDates = [];
        
        $faker = Factory::create('fr_FR');
        $now   = $faker->dateTime('now');
        
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
        return $etatsPossibles;
    }
    
    private function selectionnerEtat(Sortie $sortie): Etat {
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
        $this->filtreEtatsNbParticipants($sortie, $etatsPossibles);
        
        // États possibles en fonction de la date
        $this->filtreEtatsDates($sortie, $etatsPossibles);
        
        $choixEtat = array_rand($etatsPossibles);
        
        /** @var Etat $etat */
        $etat = $this->getReference($etatsPossibles[$choixEtat]);
        
        $sortie->setEtat($etat);
        
        return $etat;
    }
    
    private function genererSortie(
        ObjectManager     $manager,
        string            $nom = null,
        Participant       $organisateur = null,
        int               $nombrePlaces = null,
        DateTimeInterface $dateFinInscriptions = null,
        DateTimeInterface $dateDebut = null,
        int               $duree = null,
        Lieu              $lieu = null,
        string            $infos = null,
        array             $participants = null,
    ): Sortie {
        $sortie = new Sortie();
        $faker  = Factory::create('fr_FR');
        
        if(!$nom) $nom = $faker->unique()->realTextBetween(5, 20);
        
        if(!$organisateur) {
            /** @var Participant $organisateur */
            $organisateur = $this->getReference(
                ParticipantsFixtures::REF_PREFIX .
                random_int(0, ParticipantsFixtures::$count - 1),
            );
        }
        
        if(!$nombrePlaces) $nombrePlaces = $faker->numberBetween(self::NB_PLACES_MIN, self::NB_PLACES_MAX);
        
        if(!$dateFinInscriptions) $dateFinInscriptions = $faker->dateTimeBetween(self::DATE_MIN, self::DATE_MAX);
        if(!$dateDebut) $dateDebut = $faker->dateTimeBetween($dateFinInscriptions, self::DATE_MAX);
        
        if(!$duree) $duree = $faker->numberBetween(1, 16) * 15;
        
        if(!$lieu) {
            /** @var Lieu $lieu */
            $lieu = $this->getReference(
                LieuxFixtures::REF_PREFIX .
                random_int(0, LieuxFixtures::$count - 1),
            );
        }
        
        $sortie
            ->setLieu($lieu)
            ->setNom($nom)
            ->setOrganisateur($organisateur)
            ->setCampus($organisateur->getCampus())
            ->setNbInscriptionsMax($nombrePlaces)
            ->setDateLimiteInscription($dateFinInscriptions)
            ->setDateHeureDebut($dateDebut)
            ->setDuree($duree)
            ->setInfosSortie($faker->realTextBetween(20, 600))
        ;
        
        $this->inscrireParticipants(
            $sortie,
            $participants,
            self::CHANCES_INSCRITS_ZERO,
            self::CHANCES_INSCRITS_MAX,
        );
        $this->selectionnerEtat($sortie);
        
        $manager->persist($sortie);
        
        return $sortie;
    }
}
