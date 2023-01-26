<?php

namespace App\Controller;

use App\Entity\Participant;
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
//        $participant = $participantRepository->find(
//            $user->getId()
//        );
        /** @var Participant $participant */
        $participant = $user;
        
        $profileForm = $this->createForm(ProfilType::class, $participant);
        $profileForm->handleRequest($request);
        
        $motDePasseForm = $this->createForm(MotDePasseType::class, $participant);
        $motDePasseForm->handleRequest($request);
        
        if($profileForm->isSubmitted() && $profileForm->isValid()) {
            // vérification de l'unicité du pseudo
            $pseudoInput = $profileForm->get('pseudo')->getData();
            if(
                $participant->getPseudo() !== $pseudoInput &&
                $participantRepository->findOneBy(['pseudo' => $pseudoInput])
            ) {
                $this->addFlash('error', 'Ce pseudo est déjà pris #padbol');
                return $this->redirectToRoute('app_profil_monprofil');
            }
            
            // sauvegarde des nouvelles données
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
                    $motDePasseForm->get('motPasse')->getData(),
                )
            );
        }
        
        return $this->render('participant/monprofil.html.twig', [
            'profilForm'     => $profileForm->createView(),
            'motDePasseForm' => $motDePasseForm->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(
        int                   $id,
        ParticipantRepository $participantRepository,
    ) {
        $participant = $participantRepository->find($id);
        
        if(!($participant instanceof Participant)) {
            $this->addFlash('error', 'Participant non trouvé');
            return $this->redirectToRoute('app_sortie_liste');
        }
        
        return $this->render('participant/profil.html.twig', [
            'participant' => $participant,
        ]);
    }
}
