<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\SearchSortie;
use App\Entity\Sortie;
use App\Exception\BLLException;
use App\Form\SearchSortieType;
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
    public function liste(SortieRepository $sortieRepository, UserInterface $user,Request $request): Response {
        /** @var  Participant $user */
        $sorties = $sortieRepository->findAllActiveByCampus($user);

        $search = new SearchSortie();
        $searchForm = $this->createForm(SearchSortieType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted()){
            $scriteres = $searchForm->getData();
            dd($scriteres);
        }


        return $this->render('sortie/listeSortie.html.twig', [
            'sorties' => $sorties,
            'searchForm' => $searchForm->createView()
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
        Request $request,
        UserInterface $user,
        SortieEtatsManager $sortieEtatsManager,
    
    ): Response {

        /** @var Participant $user */
        $sortie     = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        $villeForm = $this->createForm(VilleType::class,);
        $villeForm->handleRequest($request);
        
        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortieEtatsManager->creer($sortie,$user);

            $this->addFlash('success', 'Votre sortie a bien été créée');
            return $this->redirectToRoute('app_sortie_liste');
        }
        
        return $this->render('sortie/creerSortie.html.twig', [
            'SortieForm' => $sortieForm->createView(), 'sortie' => $sortie,
        ]);
        
    }
    
    #[Route('/modifier/{id}', name: 'modifier', methods: ['GET'])]
    public function modifier(
        Sortie             $sortie,
        SortieEtatsManager $sortieManager,
    ) {
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::MODIFIER, $sortie, 'Dinaaaaaayded !!');
        
        $sortieManager->modifier($sortie);
        
        $this->addFlash('success', 'Sortie ' . $sortie->getNom() . ' modifiée!');
        return $this->redirectToRoute('app_main_home');
    }
    
    #[Route('/publier/{id}', name: 'publier', methods: ['GET'])]
    public function publier(
        int                $id,
        SortieRepository   $sortieRepository,
        SortieEtatsManager $sortieManager,
    ) {
        $sortie = $sortieRepository->find($id);
        // Controle les droits utilisateurs pour cette action
        $this->denyAccessUnlessGranted(SortieVoter::PUBLIER, $sortie, 'Dinaaaaaayded !!');
        
        $sortieManager->publier($sortie);
        
        $this->addFlash('success', 'Sortie ' . $sortie->getNom() . ' publiée!');
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
        int              $id,
        SortieRepository $sortieRepository,
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