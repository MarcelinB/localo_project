<?php

namespace App\Controller;

use App\Entity\Slot;
use App\Entity\Farm;
use App\Entity\User;
use App\Form\SlotType;
use App\Repository\FarmRepository;
use App\Repository\SlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlotController extends AbstractController
{
    #[Route('/affichage', name: 'affiche_slot')]
    public function arnaque(Request $request, SlotRepository $slotRepository, FarmRepository $farmRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $farm = $farmRepository->findOneByProducer($user);
        $slots = $slotRepository->findByFarm($farm);
        return $this->render('slot/index.html.twig', [
            'slots' => $slots,
        ]);
    }

    #[Route('/create_slot', name: 'create_slot')]
    public function create(Request $request, FarmRepository $farmRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();

        /** @var Farm $farm */
        $farm = $farmRepository->findOneByProducer($userId);

        $day = $_POST['day'];
        $hour1 = $_POST['hour1'];
        $hour2 = $_POST['hour2'];
        $minute1 = $_POST['minute1'];
        $minutes2 = $_POST['minute2'];

        $stringSlot = $day . ' de ' . $hour1 . 'h' . $minute1 .
            ' à ' . $hour2 . 'h' . $minutes2;

        $slot = new Slot();
        $slot->setFarm($farm);
        $slot->setStringSlot($stringSlot);

        $entityManager->persist($slot);
        $entityManager->flush();


        return $this->redirectToRoute('affiche_slot');
    }
    #[Route('/delete_slot/{id}', name: 'delete_slot')]
    public function delete($id, SlotRepository $slotRepository, FarmRepository $farmRepository, EntityManagerInterface $entityManager): Response
    {

        $slot = $slotRepository->findOneById($id);
        $slotRepository->remove($slot);

        return $this->redirectToRoute('affiche_slot');
    }
}
