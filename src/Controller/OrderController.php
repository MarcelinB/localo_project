<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Repository\OrderLineRepository;
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
    public function create(OrderLineRepository $orderLineRepository, OrderRepository $orderRepository, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Déclaration des variables

        $id = $_POST['id'];
        $qtt = $_POST['qtt'];
        $product = $productRepository->findOneById($id);
        $productPrice = $product->getPrice();
        $farm = $product->getFarm();
        $prixPanier = 0;

        $stockProducts = $productRepository->findByFarmId($farm->getId());
        $flag = false;
        $flagOrderLine = false;

        if ($_POST['mesure'] === 'kg') {
            $qtt = $qtt * 1000;
        }
        $orderLinePrice = ($productPrice * $qtt) / 1000;

        $orders = $orderRepository->findByIdCustomer($this->getUser());
        $orderId = null;
        $flagPanier = false;


        // Si commande en cours je flag à true et je trouve l'id de cette commande

        foreach ($orders as $testorder) {
            if (($testorder->getCustomer() === $this->getUser()) && ($testorder->getState() === 'Achat')) {
                $flag = true;
                $orderTest = $testorder;
                $orderId = $testorder->getId();

                // Initialisation d'un flag pour éviter qu'un panier concerne plusieurs fermes.
                if ($product->getFarm() !== $orderTest->getFarm()) {
                    $flagPanier = true;
                }
            }
        }


        // Je trouve toutes les lignes de commandes de cette commande

        $tOrderLine = $orderLineRepository->findByOrder($orderId);

        // Pour toutes les lignes de commande si elle concerne l'article sur lequel le client vient de cliquer,
        // j'ajoute la quantité à cette ligne de commande, sinon nouvelle ligne de commande.

        foreach ($tOrderLine as $orderLine) {

            if ($orderLine->getProduct() === $product) {
                if ($qtt > $product->getQuantity()) {
                    $this->addFlash(
                        'success',
                        'Stock de ' . $product->getName() . ' insuffisant ! Stock disponible : ' . $product->getQuantity() . ' g'
                    );
                } else {
                    $orderLine->setQuantity($orderLine->getQuantity() + $qtt);
                    $orderLine->setPrice($orderLine->getPrice() + $orderLinePrice);
                    $product->setQuantity($product->getQuantity() - $qtt);
                    $flagOrderLine = true;
                }
            }
        }
        if (!$flagPanier) {
            if (!$flagOrderLine) {
                if ($qtt > $product->getQuantity()) {
                    $this->addFlash(
                        'success',
                        'Stock de ' . $product->getName() . ' insuffisant ! Stock disponible : ' . $product->getQuantity() . ' g'
                    );
                } else {
                    $orderLine = new OrderLine();
                    $orderLine->setProduct($product);
                    $orderLine->setQuantity($qtt);
                    $product->setQuantity($product->getQuantity() - $qtt);
                    $orderLine->setPrice($orderLinePrice);
                }
            }
        } else {
            $this->addFlash(
                'success',
                'Votre panier concerne une autre ferme. Veuillez vider votre panier ou le valider'
            );
        }

        // Si le client n'a aucune commande en cours j'en créé une.
        if ((!$flag) ||  ($orders === [])) {
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

        $entityManager->persist($product);
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
            'prixPanier' => $prixPanier,
        ]);
    }
    #[Route('/delete_orderline/{id}', name: 'delete_orderline', requirements: ['id' => '\d+'])]
    public function delete($id, OrderLineRepository $orderLineRepository, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $orderLine = $orderLineRepository->findOneById($id);
        $qtt = $orderLine->getQuantity();
        $product = $orderLine->getProduct();
        $product->setQuantity($product->getQuantity() + $qtt);
        $order = $orderRepository->findOneByIdCustomerAndState($this->getUser());
        $idFarm = $order->getFarm()->getId();

        $orderLineRepository->remove($orderLine);
        $entityManager->persist($product);
        $entityManager->flush();
        $tOrdersLine = $orderLineRepository->findByOrder($order);
        if ($tOrdersLine === []) {
            $orderRepository->remove($order);
        }

        return $this->redirectToRoute(
            'stock_farm',
            [
                'id' => $idFarm,
            ]
        );
    }
    #[Route('/valide_order', name: 'valide_order')]
    public function valideOrder(OrderRepository $orderRepository, OrderLineRepository $orderLineRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->findOneByIdCustomerAndState($this->getUser());
        $orderPrice = 0;
        $tOrderLines = $orderLineRepository->findByOrder($order);
        foreach ($tOrderLines as $orderLine) {
            $orderPrice = $orderPrice + $orderLine->getPrice();
        }
        $order->setPrice($orderPrice);
        $order->setState('En Attente');
        $order->setOrderedAt(new DateTimeImmutable());
        $entityManager->flush();
        return $this->redirectToRoute('list_orders');
    }

    #[Route('/list_orders', name: 'list_orders')]
    public function listOrder(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findByIdCustomer($this->getUser());

        return $this->render('order/listOrders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/accept_order/{id}', name: 'accept_order', requirements: ['id' => '\d+'])]
    public function acceptOrder($id, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->findOneById($id);
        $order->setAcceptedAt(new DateTimeImmutable());
        if ($order->getState() !== 'Refusée') {
            $order->setState('Acceptée');
            $entityManager->flush();
        }
        return $this->redirectToRoute('list_orders_farm');
    }

    #[Route('/refuse_order/{id}', name: 'refuse_order', requirements: ['id' => '\d+'])]
    public function refuseOrder($id, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->findOneById($id);
        $order->setRefusedAt(new DateTimeImmutable());
        if ($order->getState() !== 'Acceptée') {
            $order->setState('Refusée');
            $entityManager->flush();
        }
        return $this->redirectToRoute('list_orders_farm');
    }
    #[Route('/detail_order/{id}', name: 'detail_order', requirements: ['id' => '\d+'])]
    public function detailOrder($id, OrderLineRepository $orderLineRepository): Response
    {
        
       $ordersLines = $orderLineRepository->findByOrder($id);
        return $this->render('order/detailOrder.html.twig', [
            'ordersLines'=>$ordersLines,
        ]);

    }
}
