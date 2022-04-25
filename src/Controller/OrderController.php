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

        $stockProducts = $productRepository->findByFarmId($farm->getId());
        $flag = false;
        $flagOrderLine = false;

        if ($_POST['mesure'] === 'kg') {
            $qtt = $qtt * 1000;
        }
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
        ]);
    }
}
