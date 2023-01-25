<?php

namespace App\Controller;

use App\Form\MotDePasseType;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/profil', name: 'app_profil_')]
class ProfilController extends AbstractController {
    #[Route('/monprofil', name: 'monprofil', methods: ['GET', 'POST'])]
    public function monprofil(
        UserInterface               $user,
        Request                     $request,
        EntityManagerInterface      $entityManager,
        ParticipantRepository       $participantRepository,
        UserPasswordHasherInterface $userPasswordHasher,
    ): Response {
        $participant = $participantRepository->find(
            $user->getId()
        ); //@TODO: demander au prof si on ne peut pas cast $user à la place
        
        $profileForm = $this->createForm(ProfilType::class, $participant);
        $profileForm->handleRequest($request);
        
        $motDePasseForm = $this->createForm(MotDePasseType::class, $participant);
        $motDePasseForm->handleRequest($request);
        
        if($profileForm->isSubmitted() && $profileForm->isValid()) {
            // sauvegarde des nouvelles données
            //@TODO: trouver comment ne pas mettre à NULL les champs vides
            $entityManager->persist($participant);
            $entityManager->flush();
            
            $this->addFlash('success', 'Profil modifié avec succès! Bien joué');
            return $this->redirectToRoute('app_main_home');
        }
        
        if($motDePasseForm->isSubmitted() && $motDePasseForm->isValid()) {
            // hashage du mot de passe
            $participant->setMotPasse(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $profileForm->get('motPasse')->getData(),
                )
            );
        }
        
        return $this->render('participant/profil.html.twig', [
            'profilForm'     => $profileForm->createView(),
            'motDePasseForm' => $motDePasseForm->createView(),
        ]);
    }
}
