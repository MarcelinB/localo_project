<?php

namespace App\Controller;

use App\Entity\Farm;
use App\Entity\User;
use App\Form\CreateFarmType;
use App\Repository\FarmRepository;
use App\Repository\OrderLineRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class FarmController extends AbstractController
{
    #[Route('/farm/create', name: 'create_farm')]
    public function create(Request $request, EntityManagerInterface $entityManager, string $uploadDir): Response
    {
        $farm = new Farm();

        /** @var User $user */
        $user = $this->getUser();

        $farm->setProducer($user);
        $user->setGotAFarm(true);

        $form = $this->createForm(CreateFarmType::class, data: $farm);
        $form->handleRequest($request);
        
        if($form->isSubmitted()&& $form->isValid()){
            $farm->setImage(sprintf('%s.%s', Uuid::v4(), $farm->getImageFile()->getClientOriginalExtension()));
            $farm->getImageFile()->move(directory: $uploadDir, name: $farm->getImage());
            $entityManager->persist($farm);
            $entityManager->flush();

            return $this->redirectToRoute(route:'index', parameters: [
                
            ]);
        }


        return $this->renderForm('farm/create.html.twig', parameters: [
            'formFarm'=> $form
        ]);
    }

    #[Route('/farm/consult', name: 'consult_farm')]
    public function consult(FarmRepository $farmRepository)
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();
        $farm = $farmRepository->findOneByProducer($userId);
        
        return $this->render('farm/consult.html.twig', [
            'farm'=>$farm
        ]
    );

    }

    #[Route('/farm/list', name: 'list_farm')]
    public function list(FarmRepository $farmRepository)
    {
        $ville = $_POST['ville'];
        $farms = $farmRepository->findByCity($ville);
        return $this->render('farm/list.html.twig', [
            'farms'=>$farms,
        ]
    );
    }

    #[Route('/farm/recherche', name: 'recherche_farm')]
    public function recherche()
    {     
        $farms = [];
        return $this->render('farm/list.html.twig', [
            'farms'=>$farms,
        ]
    );
    }

    #[Route('/farm/{id}/stock', name: 'stock_farm', requirements: ['id' => '\d+'])]
    public function listStock(OrderLineRepository $orderLineRepository, OrderRepository $orderRepository, Farm $farm, ProductRepository $productRepository)
    {
       $idFarm = $farm->getId();
        $products = $productRepository->findByFarmId($idFarm);
        $panier = $orderRepository->findOneByIdCustomerAndState($this->getUser());
        $productsPanier = [];
        if ($panier <> null)
        {
            $productsPanier = $orderLineRepository->findByOrder($panier);
        } 
        $tPanier = [];
        foreach ($productsPanier as $productPanier)
        {
            array_push($tPanier, $productPanier->getProduct());
        }
        return $this->render('farm/stock.html.twig', [
            'products'=>$products,
            'productsPanier'=>$tPanier,
            'orderLines'=>$productsPanier,
        ]
    );
    }

}
