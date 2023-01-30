<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\InscriptionType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
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
    public function liste(SortieRepository $sortieRepository,UserInterface $user ): Response {
        /** @var  Participant $user */
        $sorties = $sortieRepository->findAllActiveByCampus($user);


//        dd($sorties);
//        dd($sorties);
        return $this->render('sortie/listeSortie.html.twig', [
            "sorties" => $sorties,
        ]);
    }

    #[Route('/listeFilstres', name: 'listeFiltres')]
    public  function listeFiltres(SortieRepository $sortieRepository) : Response{
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
        EntityManagerInterface $entityManager,
        WorkflowInterface $sortieStateMachine,
        VilleRepository $villeRepository,

    ): Response {

        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);


        $villes = $villeRepository->findAll();
        $villeForm = $this->createForm(VilleType::class,);
        $villeForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $etatCreee = $entityManager->getRepository(Etat::class)->findOneBy(['libelle'=>Etat::LABEL_CREEE]);

            //TODO completer

            //TODO récuperer la liste des villes
            //$sortie->getLieu()->getVille();
            //TODO récuperer la liste des utilisateur
            //TODO récuperer le campus de l'utilisateur
            //TODO set le lieu pour tester la création


            //$sortie->setCampus($sortieForm->get('campus')->getData());
            //$sortie->setVille($sortieForm->get('ville')->getData());

            // récuperer l'id utilisateur pour définir l'organisateur
            $sortie->setOrganisateur($this->getUser());
            // mettre l'état de la sortie à créer
            // récuperer l'état créée puis l'affecter à la sortie créer

            $sortie->setEtat($etatCreee);
            //$sortieStateMachine->

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre sortie a bien été créée');
            return $this->redirectToRoute('app_sortie_liste');

        }

        return $this->render('sortie/creerSortie.html.twig', [
            'SortieForm' => $sortieForm->createView(),'sortie'=>$sortie,
        ]);

    }
    
    
    #[Route('/publier/{id}', name: 'publier', methods: ['GET'])]
    public function publier(
        int                $id,
        SortieRepository   $sortieRepository,
        SortieEtatsManager $sortieTransitionsManager,
    ) {
        $sortie = $sortieRepository->find($id);
        
        $sortieTransitionsManager->publier($sortie);
        
        $this->addFlash('success', 'Sortie publiée!');
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
        
        if(!$sortie) {
            $this->addFlash('error', 'La sortie n\'existe pas');
            return $this->redirectToRoute('app_main_home');
        }
        
        return $this->render('sortie/detailSortie.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}