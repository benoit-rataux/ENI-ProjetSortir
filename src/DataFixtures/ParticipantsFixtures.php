<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantsFixtures extends Fixture implements DependentFixtureInterface {
    const REF_LABEL = 'participant_';
    public static int $count = 0;
    private Generator $faker;
    
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
        $this->faker = Factory::create('fr_FR');
    }
    
    public function load(ObjectManager $manager): void {
        /** @var Campus $podlar */
        /** @var Campus $ocean */
        /** @var Campus $lespace */
        $podlar  = $this->getReference(CampusFixtures::REF_PREFIX . 'podlar');
        $ocean   = $this->getReference(CampusFixtures::REF_PREFIX . 'ocean');
        $lespace = $this->getReference(CampusFixtures::REF_PREFIX . 'lespace');
        
        $participant = $this->saveUser(
            $manager,
            'admin',
            'Albus',
            'Dumbledore',
            $podlar,
            'Admin123',
            'leperefourras@fortboyard.com',
            '0000000001',
            true,
        );
        $this->addReference(ParticipantsFixtures::REF_LABEL . self::$count++, $participant);
        
        $participant = $this->saveUser(
            $manager,
            'Garry911',
            'Garry',
            'Podbeur',
            $podlar,
            'Quais9.3/4',
            'garry911@labretagne.beur',
            '0900000304',
        );
        $this->addReference(ParticipantsFixtures::REF_LABEL . self::$count++, $participant);
        
        $participant = $this->saveUser(
            $manager,
            'La petite Sirene',
            'Arielle',
            'sushi',
            $ocean,
            'Arielle7',
        );
        $this->addReference(ParticipantsFixtures::REF_LABEL . self::$count++, $participant);
        
        $participant = $this->saveUser(
            $manager,
            'Spaaaaaace!',
            'Elon',
            'Musk',
            $lespace,
            'Spaaaaaac3!',
            'on_est_dans_l_espace@spaaaace.com',
        );
        $this->addReference(ParticipantsFixtures::REF_LABEL . self::$count++, $participant);
        
        $manager->flush();
    }
    
    private function saveUser(
        ObjectManager $manager,
        string        $pseudo,
        string        $prenom,
        string        $nom,
        Campus        $campus,
        string        $motPasse,
        string        $mail = null,
        string        $telephone = null,
        bool          $isAdmin = false,
    ): Participant {
        $mail      = $mail !== null ? $mail : $this->faker->email();
        $telephone = $telephone !== null ? $telephone : $this->faker->PhoneNumber();
        
        $user = new Participant();
        $user->setPseudo($pseudo);
        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setActif(true);
        $user->setAdministrateur($isAdmin);
        $user->setCampus($campus);
        $user->setMail($mail);
        $user->setTelephone($telephone);
        $user->setMotPasse(
            $this->userPasswordHasher->hashPassword(
                $user,
                $motPasse,
            )
        );
        
        $manager->persist($user);
        $this->addReference('participant_' . $pseudo, $user);
        
        $manager->flush();
        
        return $user;
    }
    
    public function getDependencies() {
        return [
            CampusFixtures::class,
        ];
    }
}
