<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Exception\BLLException;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Security\Voter\SortieVoter;
use App\Service\Workflow\SortieEtatsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/sortie', name: 'app_sortie_')]
class SortieController extends AbstractController {
    #[Route('/liste', name: 'liste')]
    public function liste(SortieRepository $sortieRepository, UserInterface $user): Response {
        /** @var  Participant $user */
        $sorties = $sortieRepository->findAllActiveByCampus($user);


//        dd($sorties);
//        dd($sorties);
        return $this->render('sortie/listeSortie.html.twig', [
            "sorties" => $sorties,
        ]);
    }
    
    #[Route('/listeFiltres', name: 'listeFiltres')]
    public function listeFiltres(SortieRepository $sortieRepository): Response {
        $sorties = $sortieRepository->findByOrganisateur();
        
        return $this->render('sortie/listeSortie.html.twig', [
            "sorties" => $sorties,
        ]);
    }

//    #[Route('/voirDetails/{id}',name: 'voirDetails')]
//    public function voirDetails($id){
//
//    }
    
    #[Route('/creer', name: 'creer', methods: ['GET', 'POST'])]
    public function creerSortie(
        Request                $request,
        EntityManagerInterface $entityManager,
        WorkflowInterface      $sortieStateMachine,
        VilleRepository        $villeRepository,
    
    ): Response {
        
        $sortie     = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        
        
        $villes    = $villeRepository->findAll();
        $villeForm = $this->createForm(VilleType::class,);
        $villeForm->handleRequest($request);
        
        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            
            $etatCreee = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::LABEL_CREEE]);
            
            //TODO completer
            //TODO mettre à zéro le nombre initial d'inscrit pour les sorties
            $sortie->setNbInscriptionsMax(0);
            //TODO récuperer la liste des villes
            //$sortie->getLieu()->getVille();
            //TODO récuperer l'organisateur
            $organisateur = $entityManager->getRepository(Participant::class)->find($this->getUser()->getId());
            //TODO récuperer le campus de l'utilisateur
            $sortie->setCampus($this->getUser()->getCampus());
            //TODO set le lieu pour tester la création

            // récuperer l'id utilisateur pour définir l'organisateur
            $sortie->setOrganisateur($organisateur);

            // mettre l'état de la sortie à créer
            // récuperer l'état créée puis l'affecter à la sortie créée
            
            $sortie->setEtat($etatCreee);
            //$sortieStateMachine->
            
            $entityManager->persist($sortie);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre sortie a bien été créée');
            return $this->redirectToRoute('app_sortie_liste');
            
        }
        
        return $this->render('sortie/creerSortie.html.twig', [
            'SortieForm' => $sortieForm->createView(), 'sortie' => $sortie,
        ]);
        
    }
    
    
    #[Route('/publier/{id}', name: 'publier', methods: ['GET'])]
    public function publier(
        int                $id,
        SortieRepository   $sortieRepository,
        SortieEtatsManager $sortieTransitionsManager,
    ) {
        $sortie = $sortieRepository->find($id);
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::PUBLIER, $sortie, 'Dinaaaaaayded !!');
        
        $sortieTransitionsManager->publier($sortie);
        
        $this->addFlash('success', 'Sortie publiée!');
        return $this->redirectToRoute('app_main_home');
    }
    
    
    #[Route('/sinscrire/{id}', name: 'sinscrire', methods: ['GET'])]
    public function sinscrire(
        int                $id,
        SortieRepository   $sortieRepository,
        SortieEtatsManager $sortieTransitionsManager,
        UserInterface      $participantConnecte,
    ) {
        $sortie = $sortieRepository->find($id);
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::SINSCRIRE, $sortie, 'Dinaaaaaayded !!');
        
        /** @var Participant $participantConnecte */
        
        try {
            $sortieTransitionsManager->sinscrire($sortie, $participantConnecte);
        } catch(BLLException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_main_home');
//            return $this->redirectToRoute('app_sortie_detail', [
//                'id' => $id
//            ]);
        }
        
        $this->addFlash('success', 'Vous êtes inscrit à la sortie "' . $sortie->getNom() . '" ! Amusez-vous bien !');
        return $this->redirectToRoute('app_main_home');
//        return $this->redirectToRoute('app_sortie_detail', [
//            'id' => $id
//        ]);
    }
    
    #[Route('/sedesinscrire/{id}', name: 'sedesister', methods: ['GET'])]
    public function seDesister(
        int                $id,
        SortieRepository   $sortieRepository,
        SortieEtatsManager $sortieTransitionsManager,
        UserInterface      $participantConnecte,
    ) {
        $sortie = $sortieRepository->find($id);
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::SE_DESISTER, $sortie, 'Dinaaaaaayded !!');
        
        /** @var Participant $participantConnecte */
        
        try {
            $sortieTransitionsManager->seDesinscrire($sortie, $participantConnecte);
        } catch(BLLException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_main_home');
        }
        
        $this->addFlash('success', "Vous n'êtes plus inscrit à la sortie \"" . $sortie->getNom() . '" !');
        return $this->redirectToRoute('app_main_home');
    }
    
    #[Route('/annuler/{id}', name: 'annuler', methods: ['GET'])]
    public function annuler(
        int                $id,
        SortieRepository   $sortieRepository,
        SortieEtatsManager $sortieTransitionsManager,
        UserInterface      $participantConnecte,
    ) {
        $sortie = $sortieRepository->find($id);
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::ANNULER, $sortie, 'Dinaaaaaayded !!');
        
        /** @var Participant $participantConnecte */
        
        try {
            $sortieTransitionsManager->annuler($sortie, $participantConnecte);
        } catch(BLLException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_main_home');
        }
        
        $this->addFlash('success', "Vous n'êtes plus inscrit à la sortie \"" . $sortie->getNom() . '" !');
        return $this->redirectToRoute('app_main_home');
    }
    
    #[Route('/detail/{id}', name: 'detail', methods: ['GET', 'POST'])]
    public function detail(
        int                    $id,
        UserInterface          $user,
        Request                $request,
        SortieRepository       $sortieRepository,
        EntityManagerInterface $entityManager,
    ) {
        $sortie = $sortieRepository->find($id);
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::AFFICHER, $sortie, 'Dinaaaaaayded !!');
        
        if(!$sortie) {
            $this->addFlash('error', 'La sortie n\'existe pas');
            return $this->redirectToRoute('app_main_home');
        }
        
        return $this->render('sortie/detailSortie.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}