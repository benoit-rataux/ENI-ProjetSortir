<?php

namespace App\Controller;

use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/profil', name: 'app_profil_')]
class ProfilController extends AbstractController {
    #[Route('/monprofil', name: 'monprofil', methods: ['GET', 'POST'])]
    public function monprofil(
        UserInterface          $participant,
        Request                $request,
        ParticipantRepository  $participantRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $participantForm = $this->createForm(ProfilType::class, $participant);
        $participantForm->handleRequest($request);
        
        if($participantForm->isSubmitted() && $participantForm->isValid()) {
            // sauvegarde des nouvelles données
            $entityManager->persist($participant);
            $entityManager->flush();
            
            $this->addFlash('success', 'Profil modifié avec succès! Bien joué');
            return $this->redirectToRoute('app_main_home');
        }
        
        return $this->render('participant/profil.html.twig', [
            'profilForm' => $participantForm->createView(),
        ]);
    }
}
