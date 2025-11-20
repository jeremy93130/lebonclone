<?php

namespace App\Controller;

use App\Entity\Command;
use App\Entity\ProductCommand;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CommandController extends AbstractController
{
    #[Route('/command', name: 'app_command')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository, EntityManagerInterface $entityManagerInterface): Response
    {
        // On récupère la session panier
        $cart = $session->get('cart', []);

        // On va récupérer nos produits dans la BDD
        $products = [];
        foreach ($cart as $key => $inutile) {
            $products[] = $productsRepository->find($key);
        }


        // On créer une variable total avec une valeur à 0 pour calculer le total après
        $total = 0;

        // On créer un objet command qu'on rempli avec les informations dont on dispose
        $command = new Command();
        $command->setAdress("13 rue de l'amour")->setZipCode(85100)->setCity('Les sables-d\'olonne');

        // On boucle sur chaque produit
        foreach ($products as $pr) {
            // On créer l'objet ProductCommand (pour chaque produit)
            $productCommand = new ProductCommand();

            // On le rempli avec le produit concerné, l'utilisateur
            $productCommand->setProduct($pr);
            $productCommand->setUser($this->getUser());

            // On relie l'objet ProductCommand à la commande qu'on vient de créer (ligne 33)
            $command->addProductCommand($productCommand);

            // On enregistre à chaque tour de boucle l'objet productCommand (il y'en aura autant qu'il y'a de produits)
            $entityManagerInterface->persist($productCommand);

            // On calcule le total (total = total + le prix de chaque produit)
            $total += $pr->getPrice();
            // On spécifie que le produit a été vendu (à true)
            $pr->setSold(true);
            // On enregistre la modification de l'objet Produit (pour chaque produit vu qu'on est dans une boucle)
            $entityManagerInterface->persist($pr);
        }
        // On donne le total à l'objet command
        $command->setTotalPrice($total);
        // On enregistre l'objet commande
        $entityManagerInterface->persist($command);
        // On envoie toutes les modifications (tous les persists)
        $entityManagerInterface->flush();

        // On vide le panier
        $session->remove('cart');
        // On redirige vers la page qui confirme que la commande a bien été traitée
        return $this->redirectToRoute('app_confirm');
    }


    #[Route('/confirm', name: 'app_confirm')]
    public function confirm()
    {
        return $this->render('command/index.html.twig', [
            'controller_name' => 'CommandController',
            'url' => $this->generateUrl('app_home'),
            'delay' => 10
        ]);
    }
}
