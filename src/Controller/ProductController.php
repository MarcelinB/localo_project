<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Farm;
use App\Repository\FarmRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product/gestionstock', name: 'gestionstock')]
    public function gestionStock(FarmRepository $farmRepository, ProductRepository $productRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();

        /** @var Farm $farm */
        $farm = $farmRepository->findOneByProducer($userId);

        $products = $productRepository->findAll();
        
        return $this->render('product/stock.html.twig', [
            'products' => $products,
        ]);
    }
}
