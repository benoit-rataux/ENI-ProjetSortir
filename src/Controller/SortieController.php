<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\InscriptionType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/sortie', name: 'app_sortie_')]
class SortieController extends AbstractController {
    #[Route('/liste', name: 'liste')]
    public function liste(SortieRepository $sortieRepository): Response {
        $sorties = $sortieRepository->findAll();


        return $this->render('sortie/sortie.html.twig', [
            "sorties" => $sorties,
        ]);
    }

//    #[Route('/voirDetails/{id}',name: 'voirDetails')]
//    public function voirDetails($id){
//
//    }
    
    #[Route('/creer', name: 'creer')]
    public function creerSortie(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response {
        $sortie = new Sortie();
        
        $sortieForm = $this->createForm(Sortie::class, $sortie);
        $sortieForm->handleRequest($request);
        
        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            
            $sortie->setOrganisateur($this->getUser());
            //TODO completer
            
            $listEtat = $etatRepository->findOneBy(array('libelle' => 'Créée'));
            $sortie->setEtat($listEtat);
            
            $this->addFlash('success', 'Votre sortie a bien été créée');
            return $this->redirectToRoute('app_sortie');
            
        }
        
        return $this->render('sortie/creerSortie.html.twig', [
            'Sortieform' => $sortieForm->createView(),
        ]);
        
        
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
        
        if(!$sortie) {
            $this->addFlash('error', 'La sortie n\'existe pas');
            return $this->redirectToRoute('app_main_home');
        }
        
        return $this->render('sortie/detailSortie.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}
