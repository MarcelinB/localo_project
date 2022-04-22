<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Farm;
use App\Entity\Product;
use App\Form\CreateNewProductType;
use App\Form\UpdateQuantityroductType;
use App\Repository\FarmRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ProductController extends AbstractController
{
    #[Route('/product/gestionstock', name: 'gestionstock')]
    public function gestionStock(Request $request, EntityManagerInterface $entityManager, FarmRepository $farmRepository, ProductRepository $productRepository): Response
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

    #[Route('/product/create', name: 'create_product')]
    public function create(Request $request, EntityManagerInterface $entityManager, FarmRepository $farmRepository, string $uploadDir): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();

        /** @var Farm $farm */
        $farm = $farmRepository->findOneByProducer($userId);

        $product = new Product();

        $form = $this->createForm(CreateNewProductType::class, $product)->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid()){
            $product->setFarm($farm);
            $product->setImage(sprintf('%s.%s', Uuid::v4(), $product->getImageFile()->getClientOriginalExtension()));
            $product->getImageFile()->move(directory: $uploadDir, name: $product->getImage());
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute(route:'gestionstock', parameters: [
                
            ]);
        }

        
        return $this->renderForm('product/create.html.twig', [
            'formProd' => $form,
        ]);
    }

    #[Route('/product/{id}/updatestock', name: 'updatestock_product', requirements: ['id' => '\d+'])]
    public function updateStock(Product $product, EntityManagerInterface $entityManager): Response
    {
        $oldQtt = $product->getQuantity();
        $product->setQuantity($oldQtt + $_POST['qtt']);
        $entityManager->flush();
        return $this->redirectToRoute(route:'gestionstock', parameters: [
                
        ]);
    }
}
