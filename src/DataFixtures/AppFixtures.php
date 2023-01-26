<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture {
    private Generator $faker;
    
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
        $this->faker = Factory::create('fr_FR');
    }
    
    public function load(ObjectManager $manager): void {
        /*******************
         * Campus
         *******************/
        $podlar = new Campus();
        $podlar->setNom('Peau-de-Lard');
        $manager->persist($podlar);
        $this->addReference('campus_podlar', $podlar);
        
        $ocean = new Campus();
        $ocean->setNom('Soulloceiyan!');
        $manager->persist($ocean);
        $this->addReference('campus_ocean', $ocean);
        
        $lespace = new Campus();
        $lespace->setNom('L\'espace');
        $manager->persist($lespace);
        $this->addReference('campus_lespace', $lespace);
        
        /*******************
         * Participants
         *******************/
        $this->saveUser(
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
        
        $this->saveUser(
            $manager,
            'Garry911',
            'Garry',
            'Podbeur',
            $podlar,
            'Quais9.3/4',
            'garry911@labretagne.beur',
            '0900000304',
        );
        
        $this->saveUser(
            $manager,
            'La petite Sirene',
            'Arielle',
            'sushi',
            $ocean,
            'Arielle7',
        );
        
        $this->saveUser(
            $manager,
            'Spaaaaaace!',
            'Elon',
            'Musk',
            $lespace,
            'Spaaaaaac3!',
            'on_est_dans_l_espace@spaaaace.com',
        );
        
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
    ) {
        $user      = new Participant();
        $mail      = $mail !== null ? $mail : $this->faker->email();
        $telephone = $telephone !== null ? $telephone : $this->faker->PhoneNumber();
        
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
    }
}
