<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Repository\OrderRepository;
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
    public function create(OrderRepository $orderRepository, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $_POST['id'];
        $qtt = $_POST['qtt'];
        $product = $productRepository->findOneById($id);
        $farm = $product->getFarm();
        $_POST['idFarm'] = $farm->getId();
        $products = $productRepository->findByFarmId($farm->getId());

        if ($_POST['mesure'] === 'kg') {
            $qtt = $qtt * 1000;
        }
        $orderLine = new OrderLine();
        $orderLine->setProduct($product);
        $orderLine->setQuantity($qtt);
        $orders = $orderRepository->findByIdCustomer($this->getUser());
        if ($orders === []){
            $order = new Order();
                $order->setCustomer($this->getUser());
                $order->setFarm($farm);
                $orderLine->setOrder($order);
                
                $order->setState('Achat');
                $entityManager->persist($order);
        }
        foreach ($orders as $testorder) {
            if (($testorder->getCustomer() <> $this->getUser()) && ($testorder->getState() <> 'Achat')) {
                $order = new Order();
                $order->setCustomer($this->getUser());
                $order->setFarm($farm);
                $orderLine->setOrder($order);
                $order->setState('Achat');
                $entityManager->persist($order);
            }
            elseif (($testorder->getCustomer() === $this->getUser()) && ($testorder->getState() === 'Achat')){
                $orderLine->setOrder($testorder);
            }
        }
        
        $entityManager->persist($orderLine);
        $entityManager->flush();
        return $this->redirectToRoute('stock_farm', [
            'id' => $farm->getId(),
        ]);
    }
}
