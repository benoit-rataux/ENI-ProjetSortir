<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use DateTimeInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


class SortiesFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    
    use EntityGeneratorTrait;
    
    ////////// options \\\\\\\\\\\\\
    private const                 MIN_QUANTITY_TO_GENERATE = 10;
    private const                 DATE_MIN                 = '-120 days';
    private const                 DATE_MAX                 = '+30 days';
    private const                 NB_PLACES_MIN            = 1;
    private const                 NB_PLACES_MAX            = 12;
    private const                 CHANCES_INSCRITS_ZERO    = 0.3;    // float entre 0 et 1
    private const                 CHANCES_INSCRITS_MAX     = 0.3;    // float entre 0 et 1
    
    ////////////////////////////////
    
    
    public static function getGroups(): array {
        return ['sortie'];
    }
    
    public function load(ObjectManager $manager): void {
        $this->initialise($manager);
        
        $this->generateOne(
            'sortie de test',
            null,
            0,
        );
        
        $this->generateRemainings();
        
        $manager->flush();
    }
    
    public function getDependencies(): array {
        return [
            VilleFixtures::class,
            LieuFixtures::class,
            EtatFixtures::class,
            CampusFixtures::class,
            ParticipantFixtures::class,
            AppFixtures::class,
        ];
    }
    
    private function inscrireParticipants(
        Sortie $sortie,
        array  $participants = null,
    ): Sortie {
        
        foreach($participants as $participant) {
            /** @var Participant $participant */
            //$sortie->addParticipant($participant); // @FIXME - fixture: accès aux attributs des autres entity
        }
        
        return $sortie;
    }
    
    private function etatsPossiblesNbParticipants(Sortie $sortie): array {
        
        $nbParticipants = count($sortie->getParticipants());
        
        if($nbParticipants === 0)
            return [
                Etat::LABEL_CREEE,
                Etat::LABEL_OUVERTE,
                Etat::LABEL_ANNULEE,
            ];
        
        else if($nbParticipants >= $sortie->getNbInscriptionsMax())
            return [
                Etat::LABEL_CLOTUREE,
                Etat::LABEL_ANNULEE,
                Etat::LABEL_EN_COURS,
                Etat::LABEL_PASSEE,
                Etat::LABEL_HISTORISEE,
            ];
        
        else
            return [
                Etat::LABEL_OUVERTE,
                Etat::LABEL_CLOTUREE,
                Etat::LABEL_ANNULEE,
                Etat::LABEL_EN_COURS,
                Etat::LABEL_PASSEE,
                Etat::LABEL_HISTORISEE,
            ];
    }
    
    private function etatsPossiblesDates(Sortie $sortie): array {
        
        $now = $this->faker->dateTime('now');
        
        if($sortie->getDateLimiteInscription() > $now)
            return [
                Etat::LABEL_CREEE,
                Etat::LABEL_OUVERTE,
                Etat::LABEL_CLOTUREE,
                Etat::LABEL_ANNULEE,
            ];
        
        else if($sortie->getDateHeureDebut() > $now)
            return [
                Etat::LABEL_CLOTUREE,
                Etat::LABEL_ANNULEE,
            ];
        
        else
            return [
                Etat::LABEL_EN_COURS,
                Etat::LABEL_PASSEE,
                Etat::LABEL_ANNULEE,
                Etat::LABEL_HISTORISEE,
            ];
    }
    
    private function selectionnerEtat(Sortie $sortie): void {
        
        // États possibles en fonction du nombre d'inscrits
        $etatsPossiblesNombreInscrits = $this->etatsPossiblesNbParticipants($sortie);
        
        // États possibles en fonction de la date
        $etatsPossiblesDates = $this->etatsPossiblesDates($sortie);
        
        $etatsPossibles = array_intersect(EtatFixtures::ETATS_LABEL,
                                          $etatsPossiblesNombreInscrits,
                                          $etatsPossiblesDates);
        
        $choixEtat = array_rand($etatsPossibles);
        
        /** @var Etat $etat */
        $etat = $this->getReference($etatsPossibles[$choixEtat]);
        
        $sortie->setEtat($etat);
    }
    
    private function generateOne(
        string            $nom = null,
        Participant       $organisateur = null,
        int               $nombrePlaces = null,
        DateTimeInterface $dateFinInscriptions = null,
        DateTimeInterface $dateDebut = null,
        int               $duree = null,
        Lieu              $lieu = null,
        string            $infos = null,
        array             $participants = null,
    ): void {
        if($nombrePlaces < SortiesFixtures::NB_PLACES_MIN
            || $nombrePlaces > SortiesFixtures::NB_PLACES_MAX) {
            $nombrePlaces = null;
        }
        
        $nom                 ??= $this->faker->unique()->realTextBetween(5, 20);
        $organisateur        ??= ParticipantFixtures::getOne($this);
        $nombrePlaces        ??= $this->faker->numberBetween(SortiesFixtures::NB_PLACES_MIN, SortiesFixtures::NB_PLACES_MAX);
        $dateFinInscriptions ??= $this->faker->dateTimeBetween(SortiesFixtures::DATE_MIN, SortiesFixtures::DATE_MAX);
        $dateDebut           ??= $this->faker->dateTimeBetween($dateFinInscriptions, SortiesFixtures::DATE_MAX);
        $duree               ??= $this->faker->numberBetween(1, 16) * 15;
        $lieu                ??= LieuFixtures::getOne($this);
        $infos               ??= $this->faker->realTextBetween(20, 600);
        $participants        ??= $this->generateParticipantList($this->getRandomNumberOfParticipants($nombrePlaces));
//        $campus              = $organisateur->getCampus(); // @FIXME - fixture: accès aux attributs des autres entity
        $campus = CampusFixtures::getOne($this);
        /** @var Participant $organisateur */
        /** @var Lieu $lieu */
        
        $sortie = new Sortie();
        $sortie->setLieu($lieu)
               ->setNom($nom)
               ->setOrganisateur($organisateur)
               ->setCampus($campus)
               ->setNbInscriptionsMax($nombrePlaces)
               ->setDateLimiteInscription($dateFinInscriptions)
               ->setDateHeureDebut($dateDebut)
               ->setDuree($duree)
               ->setInfosSortie($infos)
        ;
        
        $this->inscrireParticipants($sortie, $participants);
        $this->selectionnerEtat($sortie);
        
        $this->save($sortie);
    }
    
    private function between(float $n, float $min, float $max): bool {
        return $n >= $min && $n <= $max;
    }
    
    private function getRandomNumberOfParticipants(int $max) {
        $rng        = mt_rand() / mt_getrandmax();
        $previous   = 0.;
        $currentMax = SortiesFixtures::CHANCES_INSCRITS_ZERO;
        if($this->between($rng, $previous, $currentMax)) return 0;
        
        $previous   = $currentMax;
        $currentMax += SortiesFixtures::CHANCES_INSCRITS_MAX;
        if($this->between($rng, $previous, $currentMax)) return $max;
        
        return rand(1, $max - 1);
    }
    
    private function generateParticipantList(int $nombreParticipant): array {
        $participantList = [];
        
        for($i = 0; $i < $nombreParticipant; $i++) {
            $participantList[] = ParticipantFixtures::getOne($this);
        }
        
        return $participantList;
    }
}
