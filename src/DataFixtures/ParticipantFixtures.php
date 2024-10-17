<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    
    use EntityGeneratorTrait;
    
    ////////// options \\\\\\\\\\\\\
    private const MIN_QUANTITY_TO_GENERATE = 10;
    
    ////////////////////////////////
    
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {}
    
    public static function getGroups(): array {
        return ['participant', 'sortie'];
    }
    
    public function load(ObjectManager $manager): void {
        $this->initialise($manager);
        
        /** @var Campus $podlar */
        /** @var Campus $ocean */
        /** @var Campus $lespace */
        $podlar  = CampusFixtures::getOne($this, 1);
        $ocean   = CampusFixtures::getOne($this, 2);
        $lespace = CampusFixtures::getOne($this, 3);
        
        $this->generateOne(
            'admin',
            'Albus',
            'Dumbledore',
            $podlar,
            'Admin123',
            'leperefourras@fortboyard.com',
            '0000000001',
            true,
        );
        
        $this->generateOne(
            'Garry911',
            'Garry',
            'Podbeur',
            $podlar,
            'Quais9.3/4',
            'garry911@labretagne.beur',
            '0900000304',
        );
        
        $this->generateOne(
            'La petite Sirene',
            'Arielle',
            'sushi',
            $ocean,
            'Arielle7',
        );
        
        $this->generateOne(
            'Spaaaaaace!',
            'Elon',
            'Musk',
            $lespace,
            'Spaaaaaac3!',
            'on_est_dans_l_espace@spaaaace.com',
        );
        
        $this->generateRemainings();
        
        $manager->flush();
    }
    
    public function getDependencies(): array {
        return [
            CampusFixtures::class,
        ];
    }
    
    private function generateOne(
        string $pseudo = null,
        string $prenom = null,
        string $nom = null,
        Campus $campus = null,
        string $motPasse = 'Mot2passe',
        string $mail = null,
        string $telephone = null,
        bool   $isactive = true,
        bool   $isAdmin = false,
    ): void {
        $pseudo    ??= $this->faker->name() . '_' . $this->faker->unique()->word();
        $prenom    ??= $this->faker->firstName();
        $nom       ??= $this->faker->lastname();
        $campus    ??= CampusFixtures::getOne($this);
        $mail      ??= $this->faker->email();
        $telephone ??= $this->faker->PhoneNumber();
        /** @Var Campus $campus */
        
        $participant = new Participant();
        $participant->setPseudo($pseudo);
        $participant->setPrenom($prenom);
        $participant->setNom($nom);
        $participant->setCampus($campus);
        $participant->setActif($isactive);
        $participant->setAdministrateur($isAdmin);
        $participant->setMail($mail);
        $participant->setTelephone($telephone);
        $participant->setMotPasse(
            $this->userPasswordHasher->hashPassword(
                $participant,
                $motPasse,
            ),
        );
        
        $this->save($participant);
    }
}
