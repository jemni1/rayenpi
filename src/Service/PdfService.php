<?php

namespace App\Service;

use App\Entity\Event;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

class PdfService
{
    private $pdf;
    private $twig;
    
    public function __construct(Pdf $pdf, Environment $twig)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
    }
    
    /**
     * Generate a PDF for an Event
     * 
     * @param Event $event The event entity
     * @param array $options Additional options for PDF generation
     * @return Response HTTP response with PDF
     */
    public function generateEventPdf(Event $event, array $options = []): Response
    {
        // Set default options
        $defaultOptions = [
            'filename' => 'event-'.$event->getId().'-details.pdf',
            'disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'title' => $event->getEventName().' - Details'
        ];
        
        // Merge default options with provided options
        $options = array_merge($defaultOptions, $options);
        
        // Render the template
        $html = $this->twig->render('events/pdf_template.html.twig', [
            'event' => $event,
            'title' => $options['title']
        ]);
        
        // Configure PDF options if needed
        $this->pdf->setOption('encoding', 'UTF-8');
        $this->pdf->setOption('footer-right', 'Page [page] of [topage]');
        $this->pdf->setOption('footer-font-size', 8);
        
        // Create the PDF
        $pdf = $this->pdf->getOutputFromHtml($html);
        
        // Create response
        $response = new Response($pdf);
        
        // Set headers
        $disposition = $response->headers->makeDisposition(
            $options['disposition'],
            $options['filename']
        );
        
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);
        
        return $response;
    }
}