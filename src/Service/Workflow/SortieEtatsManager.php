<?php

namespace App\Service\Workflow;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Exception\BLLException;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use DateTime;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\WorkflowInterface;

class SortieEtatsManager {
    public function __construct(
        private SortieRepository  $sortieRepository,
        private EtatRepository    $etatRepository,
        private WorkflowInterface $sortieStateMachine,
    ) {
    }
    
    public function creer(Sortie $sortie): void {
        $this->applyTransition($sortie, Etat::TRANSITION_ETAT_INITIAL);
    }
    
    public function publier(Sortie $sortie): void {
        $this->applyTransition($sortie, Etat::TRANSITION_PUBLIER);
    }
    
    /**
     * @throws BLLException
     */
    public function sinscrire(Sortie $sortie, Participant $participant): void {
        // pour être sûr que les sorties ouvertes sont bien à jour
        $this->updateDataReouvrir();
        $this->updateDataCloturer();
        
        if($sortie->getEtat()->getLibelle() !== Etat::LABEL_OUVERTE)
            throw new BLLException("Vous ne pouvez vous inscrire qu'à une sortie dont les inscriptions sont ouvertes !");
        
        if(count($sortie->getParticipants()) >= $sortie->getNbInscriptionsMax())
            throw new BLLException("Il n'y a plus de place disponibles pour la sortie " . $sortie->getNom() . '!');
        
        if(new DateTime() >= $sortie->getDateLimiteInscription()) // on ne devrait jamais tomber dans ce cas
            throw new BLLException("ERREUR DANS LE TOASTER! La date limite d'inscription est dépassée !");
        
        // ajout du participant
        $sortie->addParticipant($participant);
        
        // mise à jour de l'état de la sortie
        if(count($sortie->getParticipants()) >= $sortie->getNbInscriptionsMax())
            $this->cloturer($sortie);
    }
    
    public function annuler(Sortie $sortie): void {
        $this->applyTransition($sortie, Etat::TRANSITION_ANNULER);
    }
    
    public function commencer(Sortie $sortie): void {
        $today = new DateTime();
        
        if($today >= $sortie->getDateHeureDebut())
            $this->applyTransition($sortie, Etat::TRANSITION_COMMENCER);
    }
    
    public function terminer(Sortie $sortie): void {
        $today   = new DateTime();
        $dateFin = clone $sortie->getDateHeureDebut();
        $duree   = $sortie->getDuree();
        $dateFin->modify("+$duree minutes");
        
        if($today > $dateFin)
            $this->applyTransition($sortie, Etat::TRANSITION_TERMINER);
    }
    
    public function historiser(Sortie $sortie): void {
        $today         = new DateTime();
        $dateArchivage = clone $sortie->getDateHeureDebut();
        $dateArchivage->modify('+30 days');
        
        if($today >= $dateArchivage) {
            $this->applyTransition($sortie, Etat::TRANSITION_ARCHIVER);
        }
    }
    
    public function reouvrir(Sortie $sortie): void {
        $today             = new DateTime();
        $participantsCount = count($sortie->getParticipants());
        
        if(
            $today < $sortie->getDateLimiteInscription() &&
            $participantsCount < $sortie->getNbInscriptionsMax()
        ) {
            $this->applyTransition($sortie, Etat::TRANSITION_REOUVRIR);
        }
    }
    
    public function cloturer(Sortie $sortie): void {
        $today             = new DateTime();
        $participantsCount = count($sortie->getParticipants());
        
        if(
            $today > $sortie->getDateLimiteInscription() ||
            $participantsCount >= $sortie->getNbInscriptionsMax()
        ) {
            $this->applyTransition($sortie, Etat::TRANSITION_CLOTURER);
        }
    }
    
    
    ////// Routines de vérification et mise à jour de l'état des
    /// sorties dans la base de données
    public function updateDataReouvrir(): self {
        $sortiesAReouvrir = $this->sortieRepository->findSortiesAReouvrir();
        
        foreach($sortiesAReouvrir as $sortie) {
            try {
                $this->reouvrir($sortie);
            } catch(BLLException $e) {
                //@TODO: envoyer l'erreur dans les logs
                printf($e->getMessage());
            }
        }
        
        return $this;
    }
    
    public function updateDataCloturer(): self {
        $sortiesACloturer = $this->sortieRepository->findSortiesACloturer();
        
        foreach($sortiesACloturer as $sortie) {
            try {
                $this->cloturer($sortie);
            } catch(BLLException $e) {
                //@TODO: envoyer l'erreur dans les logs
                printf($e->getMessage());
            }
        }
        
        return $this;
    }
    
    public function updateDataCommencer(): self {
        $sortiesCommencees = $this->sortieRepository->findSortiesACommencer();
        
        foreach($sortiesCommencees as $sortie) {
            try {
                $this->commencer($sortie);
            } catch(BLLException $e) {
                //@TODO: envoyer l'erreur dans les logs
                printf($e->getMessage());
            }
        }
        
        return $this;
    }
    
    public function updateDataTerminer(): self {
        $sortiesCommencees = $this->sortieRepository->findSortiesATerminer();
        
        foreach($sortiesCommencees as $sortie) {
            try {
                $this->terminer($sortie);
            } catch(BLLException $e) {
                //@TODO: envoyer l'erreur dans les logs
                printf($e->getMessage());
            }
        }
        
        return $this;
    }
    
    public function updateDataHistoriser(): self {
        $sortiesCommencees = $this->sortieRepository->findSortiesAHistoriser();
        
        foreach($sortiesCommencees as $sortie) {
            try {
                $this->historiser($sortie);
            } catch(BLLException $e) {
                //@TODO: envoyer l'erreur dans les logs
                printf($e->getMessage());
            }
        }
        
        return $this;
    }
    
    ////////////////////////////////
    
    /**
     * @param Sortie $sortie
     * @param string $transition
     * @return void
     * @throws BLLException
     */
    private function applyTransition(Sortie $sortie, string $transition): void {
        if($transition === Etat::TRANSITION_ETAT_INITIAL) // met à état initial
            $this->sortieStateMachine->getMarking($sortie);
        
        $sortie->setEtatWorkflow($sortie->getEtat()->getLibelle());
        
        try {
            $this->sortieStateMachine->apply($sortie, $transition);
            
            $etatLibelle = $sortie->getEtatWorkflow();
            $etat        = $this->etatRepository->findOneBy(['libelle' => $etatLibelle]);
            $sortie->setEtat($etat);
            
            $this->sortieRepository->save($sortie, true);
        } catch(LogicException $e) {
            throw new BLLException(
                'impossible de ' . $transition . ' ' .
                'la sortie "' . $sortie->getNom() . '" ' .
                '[' . $sortie->getEtatWorkflow() . '] '
            );
        }
    }
}
