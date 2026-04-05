<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    // ===== LISTE =====
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findBy(
                ['active' => true],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    // ===== CRÉATION =====
#[IsGranted('ROLE_USER')]
#[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $product = new Product();
    $form = $this->createForm(ProductType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Associer le vendeur connecté
        $product->setSeller($this->getUser());

        $em->persist($product);
        $em->flush();

        $this->addFlash('success', 'Produit créé avec succès !');
        return $this->redirectToRoute('app_product_index');
    }

    return $this->render('product/new.html.twig', [
        'product' => $product,
        'form' => $form,
    ]);
}

    // ===== DÉTAIL =====
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    // ===== ÉDITION =====
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
{
    // Vérifier que c'est le vendeur OU un admin
    if ($product->getSeller() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
        $this->addFlash('danger', 'Vous ne pouvez modifier que vos propres produits.');
        return $this->redirectToRoute('app_product_index');
    }

    $form = $this->createForm(ProductType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();

        $this->addFlash('success', 'Produit mis à jour !');
        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    return $this->render('product/edit.html.twig', [
        'product' => $product,
        'form' => $form,
    ]);
}

    // ===== SUPPRESSION =====
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
{
    // Seul le vendeur ou un admin peut supprimer
    if ($product->getSeller() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
        $this->addFlash('danger', 'Vous ne pouvez supprimer que vos propres produits.');
        return $this->redirectToRoute('app_product_index');
    }

    if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
        $em->remove($product);
        $em->flush();
        $this->addFlash('warning', 'Produit supprimé.');
    }

    return $this->redirectToRoute('app_product_index');
}
    // ===== ÉDITION =====
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/buy', name: 'app_product_buy', methods: ['POST'])]
    public function buy(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        // Vérifier le stock
        $quantity = max(1, (int) $request->request->get('quantity', 1));

        if ($product->getStock() < $quantity) {
            $this->addFlash('danger', 'Stock insuffisant pour ce produit.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        // Créer la commande
        $order = new Order();
        $order->setReference('CMD-' . strtoupper(uniqid()));
        $order->setStatus('pending');
        $order->setBuyer($this->getUser());
        $order->setTotal($product->getPrice() * $quantity);

        // Créer la ligne de commande
        $line = new OrderLine();
        $line->setRelatedOrder($order);
        $line->setProduct($product);
        $line->setQuantity($quantity);
        $line->setUnitPrice($product->getPrice());

        // Décrémenter le stock
        $product->setStock($product->getStock() - $quantity);

        $em->persist($order);
        $em->persist($line);
        $em->flush();

        $this->addFlash('success', 'Commande ' . $order->getReference() . ' passée avec succès !');
        return $this->redirectToRoute('app_profile_orders');
    }
}