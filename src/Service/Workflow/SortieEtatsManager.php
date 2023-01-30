<?php

namespace App\Service\Workflow;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use DateTime;
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
            $this->reouvrir($sortie);
        }
        
        return $this;
    }
    
    public function updateDataCloturer(): self {
        $sortiesACloturer = $this->sortieRepository->findSortiesACloturer();
        
        foreach($sortiesACloturer as $sortie) {
            $this->cloturer($sortie);
        }
        
        return $this;
    }
    
    public function updateDataCommencer(): self {
        $sortiesCommencees = $this->sortieRepository->findSortiesACommencer();
        
        foreach($sortiesCommencees as $sortie) {
            $this->commencer($sortie);
        }
        
        return $this;
    }
    
    public function updateDataTerminer(): self {
        $sortiesCommencees = $this->sortieRepository->findSortiesATerminer();
        
        foreach($sortiesCommencees as $sortie) {
            $this->terminer($sortie);
        }
        
        return $this;
    }
    
    public function updateDataHistoriser(): self {
        $sortiesCommencees = $this->sortieRepository->findSortiesAHistoriser();
        
        foreach($sortiesCommencees as $sortie) {
            $this->historiser($sortie);
        }
        
        return $this;
    }
    
    ////////////////////////////////
    
    private function applyTransition(Sortie $sortie, string $transition) {
        if($transition === Etat::TRANSITION_ETAT_INITIAL) // met à état initial
            $this->sortieStateMachine->getMarking($sortie);
        
        $sortie->setEtatWorkflow($sortie->getEtat());
        
        $this->sortieStateMachine->apply($sortie, $transition);
        
        $etatLibelle = $sortie->getEtatWorkflow();
        $etat        = $this->etatRepository->findOneBy(['libelle' => $etatLibelle]);
        $sortie->setEtat($etat);
        
        $this->sortieRepository->save($sortie, true);
    }
}
