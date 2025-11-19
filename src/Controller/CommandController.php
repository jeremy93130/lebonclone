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

        $total = 0;

        foreach ($products as $pr) {
            $total += $pr->getPrice();
        }

        $command = new Command();
        $command->setAdress("13 rue de l'amour")->setZipCode(85100)->setCity('Les sables-d\'olonne')->setTotalPrice($total);

        foreach ($products as $pr) {
            $productCommand = new ProductCommand();

            $productCommand->setProduct($pr);
            $productCommand->setUser($this->getUser());

            $command->addProductCommand($productCommand);

            $entityManagerInterface->persist($productCommand);
        }

        $entityManagerInterface->persist($command);

        $entityManagerInterface->flush();

        $session->remove('cart');

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
