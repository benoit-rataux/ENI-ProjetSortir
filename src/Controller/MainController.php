<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_main_')]
class MainController extends AbstractController {
    #[Route('', name: 'home', methods: ['GET'])]
    public function home(): Response {
        return $this->redirectToRoute('app_sortie_liste');
    }
}
