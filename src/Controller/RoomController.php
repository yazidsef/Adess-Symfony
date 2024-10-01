<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use GuzzleHttp\Client;


#[Route('/room')]
final class RoomController extends AbstractController
{
    #[Route(name: 'app_room_index', methods: ['GET'])]
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_room_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ville = $room->getVille();
            $coordinates = $this->getCoordinatesFromNominatim($ville);
            if($coordinates){
                //recupérer latitude et longtitude de ville 
                $latitude = $coordinates['latitude'];
                $longitude = $coordinates['longitude'];

                //recupérer les salles pour calculer la distance paraport a la ville saisie
                // $sallesProche = $this->findRoomNear($entityManager,$latitude,$longitude);
            }
            $room->setLatitude($latitude);
            $room->setLongtitude($longitude);
            $entityManager->persist($room);
            $entityManager->flush();

            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/room/new.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    //the function to get the coordiantes of the city
    private function getCoordinatesFromNominatim(string $ville): ?array
    {
        // initilaiser guzzle pour envoyer des requettes http
        $client  =new Client();
        //preparation de l'url de Nominatil avec le nom de la ville 
        $url = "https://nominatim.openstreetmap.org/search?q=".urlencode($ville)."&format=json&limit=1";

        try{
            //envoyer des requete est réussie a nominatim
            $response = $client->request('GET', $url);

            //verification si la requette est réussie (code 200)
            if($response->getStatusCode()===200){
                //decoder la reponse JSON 
                $data = json_decode($response->getBody()->getContents(),true);

                //si les données existent retourner les cordonnées 
                if(isset($data[0])){
                    return [
                        'latitude'=>$data[0]['lat'],
                        'longitude'=>$data[0]['lon'],
                    ];
                }
            }
        }catch(\Exception $e){
            //Gérer les erreurs d'API
            $this->addFlash('error','Erreur lors de la récupération des coordonnées');
        }

    }
    
    //function to get the rooms near the city
    private function findRoomNear(EntityManagerInterface $entityManager, $latitude, $longitude): array
    {
        //recupérer les salles de la base de données
       // Utilisation de la formule de Haversine pour calculer la distance entre les salles et la ville saisie
       $query = $entityManager->createQuery(
        'SELECT s, (6371 * acos(cos(radians(:latitude)) 
        * cos(radians(s.latitude)) 
        * cos(radians(s.longitude) - radians(:longitude)) 
        + sin(radians(:latitude)) 
        * sin(radians(s.latitude)))) AS distance
        FROM App\Entity\Salle s
        HAVING distance <= 500
        ORDER BY distance ASC'
    );

    $query->setParameters([
        'latitude' => $latitude,
        'longitude' => $longitude
    ]);

    return $query->getResult();

    }

    #[Route('/{id}', name: 'app_room_show', methods: ['GET'])]
    public function show(Room $room): Response
    {
        return $this->render('room/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_room_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('room/edit.html.twig', [
            'room' => $room,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_room_delete', methods: ['POST'])]
    public function delete(Request $request, Room $room, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_room_index', [], Response::HTTP_SEE_OTHER);
    }
}
