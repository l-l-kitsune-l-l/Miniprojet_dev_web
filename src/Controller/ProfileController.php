<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profile')]
class ProfileController extends AbstractController
{
    // ===== PAGE PROFIL =====
    #[Route('/', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    // ===== MODIFIER LE PROFIL =====
    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour !');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
        ]);
    }

    // ===== MES PRODUITS EN VENTE =====
    #[Route('/products', name: 'app_profile_products', methods: ['GET'])]
    public function myProducts(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/products.html.twig', [
            'products' => $user->getProducts(),
        ]);
    }

    // ===== MES COMMANDES =====
    #[Route('/orders', name: 'app_profile_orders', methods: ['GET'])]
    public function myOrders(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/orders.html.twig', [
            'orders' => $user->getOrders(),
        ]);
    }

    // ===== MES FAVORIS =====
    #[Route('/favorites', name: 'app_profile_favorites', methods: ['GET'])]
    public function myFavorites(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/favorites.html.twig', [
            'favorites' => $user->getFavorites(),
        ]);
    }

    // ===== AJOUTER UN FAVORI =====
    #[Route('/favorites/add/{id}', name: 'app_favorite_add', methods: ['POST'])]
    public function addFavorite(Product $product, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user->getFavorites()->contains($product)) {
            $user->addFavorite($product);
            $em->flush();
            $this->addFlash('success', $product->getName() . ' ajouté aux favoris !');
        } else {
            $this->addFlash('info', 'Ce produit est déjà dans vos favoris.');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    // ===== RETIRER UN FAVORI =====
    #[Route('/favorites/remove/{id}', name: 'app_favorite_remove', methods: ['POST'])]
    public function removeFavorite(Product $product, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user->getFavorites()->contains($product)) {
            $user->removeFavorite($product);
            $em->flush();
            $this->addFlash('warning', $product->getName() . ' retiré des favoris.');
        }

        return $this->redirectToRoute('app_profile_favorites');
    }

    // ===== AVIS RECU
    #[Route('/reviews', name: 'app_profile_reviews', methods: ['GET'])]
    public function myReviews(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/reviews.html.twig', [
            'reviews' => $user->getReceivedReviews(),
        ]);
    }
}