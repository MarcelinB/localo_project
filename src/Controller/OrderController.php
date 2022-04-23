<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order_line', name: 'create_orderline')]
    public function create(ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {  
        $id = $_POST['id'];
        $qtt = $_POST['qtt'];
        $product = $productRepository->findOneById($id);
        $farm = $product->getFarm();
        $products= $productRepository->findByFarmId($farm->getId());

        if ($_POST['mesure'] === 'kg'){
            $qtt = $qtt * 1000;
        }
        $order = new Order();
        $order->setCustomer($this->getUser());
        $order->setFarm($farm);
        $order->setState('Achat');
        $entityManager->persist($order);
        $orderLine = new OrderLine();
        $orderLine->setProduct($product);
        $orderLine->setOrder($order);
        $entityManager->persist($orderLine);
        $entityManager->flush();
        return $this->render('farm/stock.html.twig', [
            'products'=>$products,
        ]);
    }
}
