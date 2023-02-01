<?php

namespace App\Security\Voter;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SortieVoter extends Voter {
    public const MODIFIER    = 'SORTIE_MODIFIER';
    public const AFFICHER    = 'SORTIE_AFFICHER';
    public const PUBLIER     = 'SORTIE_PUBLIER';
    public const ANNULER     = 'SORTIE_ANNULER';
    public const SINSCRIRE   = 'SORTIE_SINSCRIRE';
    public const SE_DESISTER = 'SORTIE_SE_DESISTER';
    
    /**
     * Liste les attributs pris en compte par le voter!
     */
    private const ATTRIBUTES = [
        self::MODIFIER,
        self::AFFICHER,
        self::PUBLIER,
        self::ANNULER,
        self::SINSCRIRE,
        self::SE_DESISTER,
    ];
    
    protected function supports(string $attribute, mixed $subject): bool {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, self::ATTRIBUTES)
            && $subject instanceof \App\Entity\Sortie;
    }
    
    protected function voteOnAttribute(
        string         $attribute,
        mixed          $subject,
        TokenInterface $token,
    ): bool {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if(
            !$user instanceof Participant ||
            !$subject instanceof Sortie
        ) {
            return false;
        }
        
        /** @var Sortie $sortie */
        $sortie = $subject;
        
        // ... (check conditions and return true to grant permission) ...
        /** @formatter:on */
        switch($attribute) {
            case self::MODIFIER:
            case self::PUBLIER:
                return in_array($sortie->getEtat()->getLibelle(), [
                        Etat::LABEL_CREEE,
                    ])
                    && $sortie->getOrganisateur()->getId() === $user->getId();
            case self::AFFICHER:
                return in_array($sortie->getEtat()->getLibelle(), [
                    Etat::LABEL_OUVERTE,
                    Etat::LABEL_CLOTUREE,
                    Etat::LABEL_EN_COURS,
                ]);
            case self::ANNULER:
                return in_array($sortie->getEtat()->getLibelle(), [
                        Etat::LABEL_OUVERTE,
                        Etat::LABEL_CLOTUREE,
                    ])
                    && $sortie->getOrganisateur()->getId() === $user->getId();
            case self::SINSCRIRE:
                return in_array($sortie->getEtat()->getLibelle(), [
                        Etat::LABEL_OUVERTE,
                    ])
                    && !$this->isInscrit($user, $sortie);
            case self::SE_DESISTER:
                return in_array($sortie->getEtat()->getLibelle(), [
                        Etat::LABEL_OUVERTE,
                    ])
                    && $this->isInscrit($user, $sortie);
            default:
                return false;
        }
        /** @formatter:on */
    }
    
    private function isInscrit(Participant $user, Sortie $sortie) {
        foreach($sortie->getParticipants() as $participant) {
            if($user->getId() === $participant->getId())
                return true;
        }
        
        return false;
    }
}
