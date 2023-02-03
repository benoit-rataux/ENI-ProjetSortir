<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\SearchSortie;
use App\Entity\Sortie;
use App\Exception\BLLException;
use App\Form\LieuType;
use App\Form\SearchSortieType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\SortieRepository;
use App\Security\Voter\SortieVoter;
use App\Service\Workflow\SortieEtatsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/sortie', name: 'app_sortie_')]
class SortieController extends AbstractController {
    #[Route('/liste', name: 'liste')]
    public function liste(SortieRepository $sortieRepository, UserInterface $user, Request $request): Response {
        /** @var  Participant $user */
        $sorties = $sortieRepository->findAllActiveByCampus($user);
        
        $search     = new SearchSortie();
        $searchForm = $this->createForm(SearchSortieType::class, $search);
        $searchForm->handleRequest($request);
        
        if($searchForm->isSubmitted()) {
            $scriteres = $searchForm->getData();
//         $sorties = $sortieRepository->findSortiesByName($search->getNomSortie());
//            $sorties = $sortieRepository->findByCampus($search->getCampus());
            $sorties = $sortieRepository->findByIntervalOfDate($search->getDebutInterval(), $search->getFinInterval());
        }
        
        return $this->render('sortie/listeSortie.html.twig', [
            'sorties'    => $sorties,
            'searchForm' => $searchForm->createView(),
        ]);
    }
    
    #[Route('/creer', name: 'creer', methods: ['GET', 'POST'])]
    public function creerSortie(
        Request            $request,
        UserInterface      $user,
        SortieEtatsManager $sortieEtatsManager,
    
    ): Response {
        
        /** @var Participant $user */
        $sortie     = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        $villeForm = $this->createForm(VilleType::class,);
        $villeForm->handleRequest($request);
        
        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            try {
                $sortieEtatsManager->creer($sortie, $user);
                $this->addFlash('success', 'Votre sortie "' . $sortie->getNom() . '" a bien été créée');
            } catch(BLLException $e) {
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('app_sortie_liste');
        }
        
        return $this->render('sortie/creerSortie.html.twig', [
            'SortieForm' => $sortieForm->createView(), 'sortie' => $sortie,
        ]);
    }
    
    #[Route('/creerLieu', name: "creerLieu", methods: ["GET", "POST"])]
    public function Lieu(Request $request, EntityManagerInterface $entityManager): Response {
        $lieu     = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);
        
        if($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_sortie_creerLieu');
        }
        
        return $this->render('sortie/creerLieu.html.twig', [
            'lieuForm' => $lieuForm->createView(),
        ]);
    }
    
    #[Route('/modifier/{id}', name: 'modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Request            $request,
        Sortie             $sortie,
        SortieEtatsManager $sortieEtatsManager,
    ) {
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::MODIFIER, $sortie, 'Dinaaaaaayded !!');
        
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        $villeForm = $this->createForm(VilleType::class,);
        $villeForm->handleRequest($request);
        
        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            try {
                $sortieEtatsManager->modifier($sortie);
                $this->addFlash('success', 'Votre sortie "' . $sortie->getNom() . '" a bien été créée');
            } catch(BLLException $e) {
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('app_sortie_liste');
        }
        
        return $this->render('sortie/modifierSortie.html.twig', [
            'SortieForm' => $sortieForm->createView(), 'sortie' => $sortie,
        ]);
    }
    
    #[Route('/supprimer/{id}', name: 'supprimer', methods: ['GET'])]
    public function supprimer(
        Sortie             $sortie,
        SortieEtatsManager $sortieEtatsManager,
    ) {
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::SUPPRIMER, $sortie, 'Dinaaaaaayded !!');
        
        try {
            $sortieEtatsManager->supprimer($sortie);
            $this->addFlash('success', 'Votre sortie "' . $sortie->getNom() . '" a bien été supprimée');
        } catch(BLLException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        
        return $this->redirectToRoute('app_main_home');
    }
    
    #[Route('/publier/{id}', name: 'publier', methods: ['GET'])]
    public function publier(
        Sortie             $sortie,
        SortieEtatsManager $sortieManager,
    ) {
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::PUBLIER, $sortie, 'Dinaaaaaayded !!');
        
        $sortieManager->publier($sortie);
        
        $this->addFlash('success', 'Votre sortie "' . $sortie->getNom() . '" a bien étée publiée!');
        return $this->redirectToRoute('app_main_home');
    }
    
    
    #[Route('/sinscrire/{id}', name: 'sinscrire', methods: ['GET'])]
    public function sinscrire(
        Sortie             $sortie,
        SortieEtatsManager $sortieTransitionsManager,
        UserInterface      $participantConnecte,
    ) {
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
        Sortie             $sortie,
        SortieEtatsManager $sortieTransitionsManager,
        UserInterface      $participantConnecte,
    ) {
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
        Sortie             $sortie,
        SortieEtatsManager $sortieTransitionsManager,
        UserInterface      $participantConnecte,
    ) {
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
        Sortie $sortie,
    ) {
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