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
    public function consult()
    {
        return $this->render('farm/consult.html.twig');

    }
}
