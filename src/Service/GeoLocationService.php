<?php
namespace App\Service;

use Psr\Http\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use GuzzleHttp\Psr7\Request;

class GeoLocationService extends AbstractController{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCoordinatesFromNominatim(string $ville): ?array
    {
        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($ville) . "&format=json&limit=1";

        try {
            $request = new Request('GET', $url);
            $response = $this->client->sendRequest($request);

            //verification if the request is successful (code 200)
            if($response->getStatusCode()===200){
                $data = json_decode($response->getBody()->getContents(),true);
            }
            if(isset($data[0])){
                return [
                    'latitude'=>$data[0]['lat'],
                    'longitude'=>$data[0]['lon'],
                ];
            }
            
        }
        catch(\Exception $e){
            $this->addFlash('error','Erreur lors de la récupération des coordonnées');
    }
}
}