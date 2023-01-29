<?php

namespace App\Service\Workflow;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use DateTime;

class SortieEtatsManager {
    public function __construct(
        private SortieRepository $sortieRepository,
        private EtatRepository   $etatRepository,
//        private WorkflowInterface $sortieStateMachine,
    ) {
    }
    
    public function creer(Sortie $sortie): void {
        $this->applyTransition($sortie, Etat::LABEL_CREEE);
    }
    
    public function publier(Sortie $sortie): void {
        $this->applyTransition($sortie, Etat::LABEL_OUVERTE);
    }
    
    public function annuler(Sortie $sortie): void {
    }
    
    public function commencer(Sortie $sortie): void {
        $today = new DateTime();
        
        if($today >= $sortie->getDateHeureDebut())
            $this->applyTransition($sortie, Etat::LABEL_EN_COURS);
    }
    
    public function terminer(Sortie $sortie): void {
        $today   = new DateTime();
        $dateFin = clone $sortie->getDateHeureDebut();
        $duree   = $sortie->getDuree();
        $dateFin->modify("+$duree minutes");
        
        if($today > $dateFin)
            $this->applyTransition($sortie, Etat::LABEL_PASSEE);
    }
    
    public function archiver(Sortie $sortie): void {
        $today         = new DateTime();
        $dateArchivage = clone $sortie->getDateHeureDebut();
        $dateArchivage->modify('+30 days');
        
        if($today >= $dateArchivage) {
            $this->applyTransition($sortie, Etat::LABEL_HISTORISEE);
        }
    }
    
    public function reouvrir(Sortie $sortie): void {
        $today             = new DateTime();
        $participantsCount = count($sortie->getParticipants());
        
        if(
            $today < $sortie->getDateLimiteInscription() &&
            $participantsCount < $sortie->getNbInscriptionsMax()
        ) {
            $this->applyTransition($sortie, Etat::LABEL_OUVERTE);
        }
    }
    
    public function cloturer(Sortie $sortie): void {
        $today             = new DateTime();
        $participantsCount = count($sortie->getParticipants());
        
        if(
            $today > $sortie->getDateLimiteInscription() ||
            $participantsCount >= $sortie->getNbInscriptionsMax()
        ) {
            $this->applyTransition($sortie, Etat::LABEL_CLOTUREE);
        }
    }
    
    
    ////// Routines de vérification et mise à jour de l'état des
    /// sorties dans la base de données
    public function updateDataCloturer(): self {
        $sortiesACloturer = $this->sortieRepository->findSortiesACloturer();
        
        foreach($sortiesACloturer as $sortie) {
            $this->cloturer($sortie);
        }
        
        return $this;
    }
    
    public function updateDataReouvrir(): self {
        $sortiesAReouvrir = $this->sortieRepository->findSortiesAReouvrir();
        
        foreach($sortiesAReouvrir as $sortie) {
            $this->reouvrir($sortie);
        }
        
        return $this;
    }
    
    ////////////////////////////////
    
    private function applyTransition(Sortie $sortie, string $etatLabel) {
        $etat = $this->etatRepository->findOneBy(['libelle' => $etatLabel]);
        $sortie->setEtat($etat);
        
        $this->sortieRepository->save($sortie, true);

//        $this->sortieStateMachine->apply();
    }
}
