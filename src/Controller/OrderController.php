<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Repository\OrderLineRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order_line', name: 'create_orderline')]
    public function create(OrderLineRepository $orderLineRepository, OrderRepository $orderRepository, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Déclaration des variables

        $id = $_POST['id'];
        $qtt = $_POST['qtt'];
        $product = $productRepository->findOneById($id);
        $farm = $product->getFarm();
        $products = $productRepository->findByFarmId($farm->getId());
        $flag = false;
        $flagOrderLine = false;

        if ($_POST['mesure'] === 'kg') {
            $qtt = $qtt * 1000;
        }
        $orders = $orderRepository->findByIdCustomer($this->getUser());
        $orderId = null;

        // Si commande en cours je flag à true et je trouve l'id de cette commande

        foreach ($orders as $testorder) {
            if (($testorder->getCustomer() === $this->getUser()) && ($testorder->getState() === 'Achat')) {
                $flag = true;
                $orderId = $testorder->getId();;
            }
        }

        // Je trouve toutes les lignes de commandes de cette commande

        $tOrderLine = $orderLineRepository->findByOrder($orderId);

        // Pour toutes les lignes de commande si elle concerne l'article sur lequel le client vient de cliquer,
        // j'ajoute la quantité à cette ligne de commande, sinon nouvelle ligne de commande.

        foreach ($tOrderLine as $orderLine) {
            if ($orderLine->getProduct() === $product) {
                $orderLine->setQuantity($orderLine->getQuantity() + $qtt);
                $flagOrderLine = true;
            }
        }
        if (!$flagOrderLine) {
            $orderLine = new OrderLine();
            $orderLine->setProduct($product);
            $orderLine->setQuantity($qtt);
        }

        // Si je n'ai aucune commandes j'en créé une.
        if ($orders === []) {
            $order = new Order();
            $order->setCustomer($this->getUser());
            $order->setFarm($farm);
            $orderLine->setOrder($order);

            $order->setState('Achat');
            $entityManager->persist($order);
        }
        // Si le client n'a aucune commande en cours j'en créé une.
        if (!$flag) {
            $order = new Order();
            $order->setCustomer($this->getUser());
            $order->setFarm($farm);
            $orderLine->setOrder($order);
            $order->setState('Achat');
            $entityManager->persist($order);
            $flag = false;
        }
        if ($flag) {
            $orderLine->setOrder($testorder);
        }

        $entityManager->persist($orderLine);
        $entityManager->flush();


        // création du tableau d'article dans le panier

        $panier = $orderRepository->findOneByIdCustomerAndState($this->getUser());
        $productsPanier = [];
        if ($panier <> null) {
            $productsPanier = $orderLineRepository->findByOrder($panier);
        }
        $tPanier = [];
        foreach ($productsPanier as $productPanier) {
            array_push($tPanier, $productPanier->getProduct());
        }

        return $this->redirectToRoute('stock_farm', [
            'id' => $farm->getId(),
            'productsPanier' => $tPanier,
            'orderLines' => $productsPanier,
        ]);
    }
}
