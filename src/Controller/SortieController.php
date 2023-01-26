<?php

namespace App\Controller;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SortieRepository;

class SortieController extends AbstractController
{
    #[Route('/sortie', name: 'app_sortie')]
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository -> findAll();
        return $this->render('sortie/sortie.html.twig', [
            "sorties" => $sorties,
        ]);
    }

    #[Route('/sortie/creer', name: 'app_sortie_creer')]
    public function creerSortie(Request $request,EntityManagerInterface $entityManager): Response{
        $sortie = new Sortie();

        $sortieForm = $this->createForm(Sortie::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $sortie->setOrganisateur($this->getUser());
            //TODO completer

        }

        return $this->render('sortie/creerSortie.html.twig', [
            'Sortieform' => $sortieForm->createView()]);


    }
}
