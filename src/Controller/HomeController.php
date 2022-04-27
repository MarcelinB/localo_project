<?php

namespace App\Controller;

use App\Repository\FarmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(FarmRepository $farmRepository): Response
    {
        $user = $this->getUser();
        $farm = $farmRepository->findOneByProducer($user);
       
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'user' => $user,
            'farm'=>$farm,
        ]);
    }
}
