<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SellerController extends AbstractController
{
    #[Route('/seller', name: 'app_seller')]
    public function index(): Response
    {
        return $this->render('seller/index.html.twig', [
            'controller_name' => 'SellerController',
        ]);
    }
}
