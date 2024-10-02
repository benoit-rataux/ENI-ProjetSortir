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
    
    ////////// options \\\\\\\\\\\\\
    private const        NB_MIN_A_GENERER = 60;
    ////////////////////////////////
    
    public const         REF_PREFIX = Participant::class . '_';
    
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
        $podlar  = $this->getReference(CampusFixtures::REF_PREFIX . '0');
        $ocean   = $this->getReference(CampusFixtures::REF_PREFIX . '1');
        $lespace = $this->getReference(CampusFixtures::REF_PREFIX . '2');
        
        $this->generateParticipant(
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
        
        $this->generateParticipant(
            $manager,
            'Garry911',
            'Garry',
            'Podbeur',
            $podlar,
            'Quais9.3/4',
            'garry911@labretagne.beur',
            '0900000304',
        );
        
        $this->generateParticipant(
            $manager,
            'La petite Sirene',
            'Arielle',
            'sushi',
            $ocean,
            'Arielle7',
        );
        
        $this->generateParticipant(
            $manager,
            'Spaaaaaace!',
            'Elon',
            'Musk',
            $lespace,
            'Spaaaaaac3!',
            'on_est_dans_l_espace@spaaaace.com',
        );
        
        $this->generateRemaining($manager);
        
        $manager->flush();
    }
    
    public function getDependencies() {
        return [
            CampusFixtures::class,
        ];
    }
    
    private function generateParticipant(
        ObjectManager $manager,
        string        $pseudo = null,
        string        $prenom = null,
        string        $nom = null,
        Campus        $campus = null,
        string        $motPasse = 'Mot2passe',
        string        $mail = null,
        string        $telephone = null,
        bool          $isAdmin = false,
    ): Participant {
        $pseudo    = $pseudo !== null ? $pseudo : $this->faker->name() . '_' . $this->faker->unique()->word();
        $prenom    = $prenom !== null ? $prenom : $this->faker->firstName();
        $nom       = $nom !== null ? $nom : $this->faker->lastname();
        $mail      = $mail !== null ? $mail : $this->faker->email();
        $telephone = $telephone !== null ? $telephone : $this->faker->PhoneNumber();
        
        if($campus == null) {
            $campus = $this->getReference(
                CampusFixtures::REF_PREFIX .
                random_int(0, CampusFixtures::$count - 1),
            );
        }
        
        $participant = new Participant();
        $participant->setPseudo($pseudo);
        $participant->setPrenom($prenom);
        $participant->setNom($nom);
        $participant->setActif(true);
        $participant->setAdministrateur($isAdmin);
        $participant->setCampus($campus);
        $participant->setMail($mail);
        $participant->setTelephone($telephone);
        $participant->setMotPasse(
            $this->userPasswordHasher->hashPassword(
                $participant,
                $motPasse,
            ),
        );
        
        $manager->persist($participant);
        $this->addReference(ParticipantsFixtures::REF_PREFIX . self::$count++, $participant);
        
        return $participant;
    }
    
    private function generateRemaining(ObjectManager $manager) {
        while(self::$count < self::NB_MIN_A_GENERER) {
            $participant = $this->generateParticipant($manager);
            $this->addReference(ParticipantsFixtures::REF_PREFIX . self::$count++, $participant);
        }
    }
}
