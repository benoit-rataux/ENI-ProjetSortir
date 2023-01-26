<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SortieRepository;

#[Route('/sortie', name:'app_sortie_')]
class SortieController extends AbstractController
{
    #[Route('/liste', name: 'liste')]
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository -> findAll();
        return $this->render('sortie/sortie.html.twig', [
            "sorties" => $sorties,
        ]);
    }

    #[Route('/creer', name: 'creer')]
    public function creerSortie(Request $request,EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response{
        $sortie = new Sortie();

        $sortieForm = $this->createForm(Sortie::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $sortie->setOrganisateur($this->getUser());
            //TODO completer

            $listEtat = $etatRepository->findOneBy(array('libelle'=>'Créée'));
            $sortie->setEtat($listEtat);

            $this->addFlash('success', 'Votre sortie a bien été créée');
            return $this->redirectToRoute('app_sortie');

        }

        return $this->render('sortie/creerSortie.html.twig', [
            'SortieForm' => $sortieForm->createView()]);


    }
}
