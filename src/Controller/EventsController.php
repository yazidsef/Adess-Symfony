<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventSearchType;
use App\Form\EventsType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GeoLocationService;


#[Route('/events')]
final class EventsController extends AbstractController
{
    private GeoLocationService $GeoLocationService;
    public function __construct(GeoLocationService $GeoLocationService)
    {
        $this->GeoLocationService = $GeoLocationService;
    }

    #[Route(name: 'app_events_index', methods: ['GET', 'POST'])]
    public function index(EventsRepository $eventsRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(EventSearchType::class);
    $form->handleRequest($request);

    // Initialisation des salles et événements
    $nearesRooms = [];
    $events = [];

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer les données du formulaire
        $data = $form->getData();
        $ville = $data['ville'];
        $coordinates = $this->GeoLocationService->getCoordinatesFromNominatim($ville);

        if ($coordinates) {
            $latitude = $coordinates['latitude'];
            $longitude = $coordinates['longitude'];

            // Trouver les salles proches avec la fonction findRoomNear
            $nearesRooms = $this->findRoomsNear($entityManager, $latitude, $longitude);
        }

        // Récupérer les événements liés aux salles proches
        if (!empty($nearesRooms)) {
            $events = $eventsRepository->findBy(['room' => $nearesRooms]);  // Assure-toi que 'room' est bien le nom de la relation dans l'entité Event
        } else {
            // Si aucune salle proche n'a été trouvée, afficher tous les événements
            $events = $eventsRepository->findAll();
        }
    } else {
        // Si le formulaire n'a pas encore été soumis, afficher tous les événements
        $events = $eventsRepository->findAll();
    }

    // Rendre la vue avec les événements récupérés
    return $this->render('events/index.html.twig', [
        'events' => $events,  // Utilise la variable $events contenant les événements filtrés
        'form' => $form->createView(),
    ]);
}

  


    //find the nearest rooms 
   private function findRoomsNear(EntityManagerInterface $entityManager, $latitude, $longitude): array
{
    $connection = $entityManager->getConnection();

    // Requête SQL brute avec la formule de Haversine
    $sql = "
        SELECT s.*, 
               (6371 * acos(cos(radians(:latitude)) 
               * cos(radians(s.latitude)) 
               * cos(radians(s.longtitude) - radians(:longitude)) 
               + sin(radians(:latitude)) 
               * sin(radians(s.latitude)))) AS distance
        FROM room s
        HAVING distance <= 500
        ORDER BY distance ASC
    ";

    // Préparation de la requête
    $stmt = $connection->executeQuery($sql, [
        'latitude' => $latitude,
        'longitude' => $longitude
    ]);

    // Utiliser fetchAllAssociative() pour récupérer les résultats
    return $stmt->fetchAllAssociative();
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
