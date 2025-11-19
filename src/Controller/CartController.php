<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        // On récupère la session 'cart'
        $cart = $session->get('cart', []);
        // On créer une variable $products à tableau vide
        $products = [];
        // On utilise les clés de la session (qui sont les id des produits) pour aller récupérer chaque produit dans la bdd
        foreach ($cart as $key => $value) {
            $products[] = $productsRepository->find($key);
        }

        // On va calculer le total ici
        $total = 0;

        foreach ($products as $pr) {
            $total += $pr->getPrice();
        }

        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'products' => $products,
            'total' => $total
        ]);
    }


    #[Route('/addCart/{id}', name: 'app_add_cart')]
    public function addToCart(int $id, SessionInterface $sessionInterface, Request $request): JsonResponse
    {
        // $sessionInterface->remove('cart');
        // SI Ajax envoie du Json (ce qui est le cas)
        $data = json_decode($request->getContent(), true); // (inutile ici)

        // On récupère la quantité envoyée par ajax
        $qty = $data['qty'] ?? 1;

        // On récupère le panier 
        // Si la session cart existe, il la récupère, sinon il créer une clé cart dans l'objet session avec en valeur un tableau vide
        $cart = $sessionInterface->get('cart', []);

        // On vérifie si l'id du produit qu'on ajoute est déjà dans la session 
        /* Structure de la session panier 
        
            $cart = [
            id du produit   quantité du produit
            1             =>  1
            ]
        */

        if (isset($cart[$id])) {
            return new JsonResponse(["length" => "This product is already in cart"]);
        } else {
            $cart[$id] = $qty;
        }

        $nb = count($cart);
        $sessionInterface->set('cart', $cart);
        return new JsonResponse(['nb' => $nb]);
    }

    #[Route('/delete/{id}', name: 'app_delete_product')]
    public function deleteProduct(int $id, SessionInterface $sessionInterface, ProductsRepository $productsRepository)
    {
        // On récupère le panier
        $cart = $sessionInterface->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]); // On supprime le produit du tableau
        } else {
            return new JsonResponse(['id' => "The id provided does not exist"]);
        }

        // On va recalculer le total ici
        $total = 0;
        $products = [];
        foreach ($cart as $key => $pr) {
            $products[] = $productsRepository->find($key);
        }

        foreach ($products as $pr) {
            $total += $pr->getPrice();
        }

        $sessionInterface->set('cart', $cart); // On met à jour le tableau de la session avec le produit supprimé en moins

        $nb = count($cart);

        return new JsonResponse(['success' => "Product deleted", 'nb' => $nb, 'total' => $total]);
    }

    #[Route('/delete', name: 'app_delete_cart')]
    public function deleteCart(SessionInterface $session)
    {
        $session->remove('cart');
        return $this->redirectToRoute('app_cart');
    }
}
