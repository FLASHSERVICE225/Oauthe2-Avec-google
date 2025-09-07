<?php
namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry): Response
    {
        // Redirection vers Google
        return $clientRegistry
            ->getClient('google')
            ->redirect(['openid', 'profile', 'email'], []); // profile permet de récupérer nom et photo
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(): Response
    {
        // Ce point sera appelé par Google après connexion
        // Ici tu gères la récupération de l'utilisateur
        return $this->redirectToRoute('app_home'); 
    }

    

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }

}
