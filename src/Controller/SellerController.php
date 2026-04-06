<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/seller')]
class SellerController extends AbstractController
{
    // ===== PAGE DU VENDEUR =====
    #[Route('/{id}', name: 'app_seller_show', methods: ['GET'])]
    public function show(User $seller, ProductRepository $productRepository): Response
    {
        // Récupérer les produits actifs du vendeur
        $products = $productRepository->findBy(
            ['seller' => $seller, 'active' => true],
            ['createdAt' => 'DESC']
        );

        // Calculer la  moyenne
        $reviews = $seller->getReceivedReviews();
        $avgRating = 0;
        if (count($reviews) > 0) {
            $total = 0;
            foreach ($reviews as $review) {
                $total += $review->getRating();
            }
            $avgRating = $total / count($reviews);
        }

        return $this->render('seller/show.html.twig', [
            'seller' => $seller,
            'products' => $products,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
        ]);
    }

    //  POSTER UN AVIS 
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/review', name: 'app_seller_review', methods: ['GET', 'POST'])]
    public function review(User $seller, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Un vendeur ne peut pas se noter lui-même
        if ($user === $seller) {
            $this->addFlash('danger', 'Vous ne pouvez pas vous noter vous-même.');
            return $this->redirectToRoute('app_seller_show', ['id' => $seller->getId()]);
        }

        // Vérifier si l'utilisateur a déjà laissé un avis sur ce vendeur
        $existingReview = $em->getRepository(Review::class)->findOneBy([
            'author' => $user,
            'seller' => $seller,
        ]);

        if ($existingReview) {
            $this->addFlash('info', 'Vous avez déjà laissé un avis sur ce vendeur.');
            return $this->redirectToRoute('app_seller_show', ['id' => $seller->getId()]);
        }

        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setAuthor($user);
            $review->setSeller($seller);

            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Merci pour votre avis !');
            return $this->redirectToRoute('app_seller_show', ['id' => $seller->getId()]);
        }

        return $this->render('seller/review.html.twig', [
            'seller' => $seller,
            'form' => $form,
        ]);
    }
}