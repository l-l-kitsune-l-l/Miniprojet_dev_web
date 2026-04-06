<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{
    #[Route('/toggle-theme', name: 'app_toggle_theme', methods: ['GET'])]
    public function toggle(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user) {
            // Utilisateur connecté  sauvegarder en BDD
            $newTheme = $user->getTheme() === 'dark' ? 'light' : 'dark';
            $user->setTheme($newTheme);
            $em->flush();
        } else {
            // Visiteur sauvegarder en session
            $session = $request->getSession();
            $currentTheme = $session->get('theme', 'light');
            $newTheme = $currentTheme === 'dark' ? 'light' : 'dark';
            $session->set('theme', $newTheme);
        }

        // Rediriger vers la page précédente
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? '/');
    }
}