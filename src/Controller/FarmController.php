<?php

namespace App\Controller;

use App\Entity\Farm;
use App\Entity\User;
use App\Form\CreateFarmType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FarmController extends AbstractController
{
    #[Route('/farm/create', name: 'create_farm')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $farm = new Farm();

        /** @var User $user */
        $user = $this->getUser();

        $farm->setProducer($user);
        $user->setGotAFarm(true);

        $form = $this->createForm(CreateFarmType::class, data: $farm);
        $form->handleRequest($request);
        
        if($form->isSubmitted()&& $form->isValid()){
            
            $entityManager->persist($farm);
            $entityManager->flush();

            return $this->redirectToRoute(route:'index', parameters: [
                
            ]);
        }


        return $this->renderForm('farm/create.html.twig', parameters: [
            'formFarm'=> $form
        ]);
    }
}
