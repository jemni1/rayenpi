<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventsType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/events')]
final class EventsController extends AbstractController
{   
    #[Route('/event/{id}/pdf', name: 'app_event_pdf', methods: ['GET'])]
    public function downloadPdf(Events $event, Pdf $knpSnappyPdf): Response
    {
        // Rendre la vue Twig en HTML
        $html = $this->renderView('events/pdf_template.html.twig', [
            'event' => $event,
        ]);

        // Générer le PDF à partir du HTML
        $pdf = $knpSnappyPdf->getOutputFromHtml($html);

        // Créer une réponse HTTP avec le contenu PDF
        $response = new Response($pdf);
        
        // Définir les en-têtes appropriés pour le téléchargement
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'event-'.$event->getId().'-details.pdf'
        );
        
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);
        
        return $response;
    }



    #[Route('/admin/events/{id}', name: 'admin_events_show', methods: ['GET'])]
    public function adminShow(Events $event, BuilderInterface $qrCodeBuilder): Response
    {
        // Generate the event details as a QR code data string
        $qrData = json_encode([
            'name' => $event->getEventName(),
            'description' => $event->getEventDescription(),
            'start_date' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $event->getEndDate()->format('Y-m-d H:i:s'),
            'location' => $event->getLocation(),
            'category' => $event->getCategory(),
        ]);
    
        // Generate the QR code
        $qrCode = $qrCodeBuilder
            ->data($qrData)
            ->size(300)
            ->margin(10)
            ->build();
    
        // Generate the data URI for the QR code image
        $qrCodeDataUri = $qrCode->getDataUri();
    
        return $this->render('adminevent/show.html.twig', [
            'event' => $event,
            'qrCode' => $qrCodeDataUri,
        ]);
    }
    


    #[Route('/admin/events', name: 'admin_events_index', methods: ['GET'])]
    public function adminIndex(Request $request, EventsRepository $eventsRepository, PaginatorInterface $paginator): Response
    {
        $searchQuery = $request->query->get('search', '');
        
        $query = $eventsRepository->createQueryBuilder('e');
        
        if (!empty($searchQuery)) {
            $query->where('e.eventName LIKE :search')
                  ->setParameter('search', '%' . $searchQuery . '%');
        }
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'e.startDate',
                'defaultSortDirection' => 'DESC',
                'sortFieldAllowList' => ['e.id', 'e.eventName', 'e.startDate', 'e.endDate', 'e.location', 'e.category']
            ]
        );
        
        if ($request->isXmlHttpRequest()) {
            return $this->render('adminevent/_table.html.twig', [
                'events' => $pagination,
                'searchQuery' => $searchQuery,
            ]);
        }
        
        return $this->render('adminevent/index.html.twig', [
            'events' => $pagination,
            'searchQuery' => $searchQuery,
        ]);
    }
    
    #[Route(name: 'app_events_index', methods: ['GET'])]
    public function index(Request $request, EventsRepository $eventsRepository, PaginatorInterface $paginator): Response
    {
        $now = new \DateTime();
        $searchQuery = $request->query->get('search', '');  // Récupérer la requête de recherche
        
        // Construire la requête avec un filtre pour le nom de l'événement
        $query = $eventsRepository->createQueryBuilder('e')
            ->where('e.startDate > :now')
            ->setParameter('now', $now);
    
        if (!empty($searchQuery)) {
            $query->andWhere('e.eventName LIKE :search')
                  ->setParameter('search', '%' . $searchQuery . '%');
        }
    
        $query->orderBy('e.startDate', 'ASC');
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
    
        // Si c'est une requête AJAX, on renvoie juste le tableau partiel
        if ($request->isXmlHttpRequest()) {
            return $this->render('events/_table.html.twig', [
                'events' => $pagination,
            ]);
        }
    
        return $this->render('events/index.html.twig', [
            'events' => $pagination,
            'searchQuery' => $searchQuery,  // Passer la valeur de recherche à la vue
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


    #[Route('/{id}', name: 'app_events_show', methods: ['GET'])]
    public function show(Events $event, BuilderInterface $qrCodeBuilder): Response
    {
        // Generate the event details as a QR code data string
        $qrData = json_encode([
            'name' => $event->getEventName(),
            'description' => $event->getEventDescription(),
            'start_date' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $event->getEndDate()->format('Y-m-d H:i:s'),
            'location' => $event->getLocation(),
            'category' => $event->getCategory(),
        ]);
    
        // Generate the QR code
        $qrCode = $qrCodeBuilder
            ->data($qrData)
            ->size(300)
            ->margin(10)
            ->build();
    
        // Generate the data URI for the QR code image
        $qrCodeDataUri = $qrCode->getDataUri();
    
        return $this->render('events/show.html.twig', [
            'event' => $event,
            'qrCode' => $qrCodeDataUri,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_events_delete', methods: ['POST'])]
    public function delete(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
