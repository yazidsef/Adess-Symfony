<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventSearchType;
use App\Form\EventsType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/events')]
final class EventsController extends AbstractController
{

    #[Route(name: 'app_events_index', methods: ['GET'])]
    public function index(EventsRepository $eventsRepository, Request $request): Response
    {
        $form = $this->createForm(EventSearchType::class);
        $form->handleRequest($request);

        //initialisation of the neares rooms
        $nearesRooms = [];
        if($form->isSubmitted()&& $form->isValid()){
            //get the data from the form
            $data = $form->getData();
            $ville = $data['ville'];
            $coordinates = $this->getCoordinatesFromNominatim($ville);

            //calling Nominatim to get the city coordinates
        }
        return $this->render('events/index.html.twig', [
            'events' => $eventsRepository->findAll(),
            'form' => $form->createView()
        ]);
    }

    #[Route('/new', name: 'app_events_new', methods: ['GET', 'POST'])]

    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Events();
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('events/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

}
