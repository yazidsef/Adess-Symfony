<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Events;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppointmentController extends AbstractController
{
    #[Route(path:'admin/appointment' ,name: 'app_appointment_index', methods: ['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointmentRepository->findAll(),
        ]);
    }


    /* La méthode pour prendre un rdv */
    #[Route('appointment/new/{event}', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager , Events $event): Response
    {
        if (!$event) {
            throw $this->createNotFoundException('L\'événement n\'existe pas.');
        }
        $appointment = new Appointment();
        $appointment->setEvent($event);
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

 
   
}
