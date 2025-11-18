<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $lastProducts = $productsRepository->lastFiveProducts();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'lastProducts' => $lastProducts
        ]);
    }
}
