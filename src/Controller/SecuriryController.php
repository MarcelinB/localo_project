<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Producer;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecuriryController extends AbstractController
{
    #[Route('/registration/{role}', name: 'registration')]
    public function Registration(string $role, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = Producer::ROLE === $role ? new Producer() : new Customer();

        $form = $this->createForm(type: RegistrationType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form-> isValid()){
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(type: "success", message: "Inscription ok");
            return $this->redirectToRoute('index');
        }
        
        return $this->render('securiry/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
