<?php

namespace App\Controller;

use App\Entity\Route;
use App\Form\RouteType;
use App\Repository\RouteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as Routing;

#[Routing('/route')]
final class RouteController extends AbstractController
{
    #[Routing('/admin', name: 'route_indexx', methods: ['GET'])]
    public function indexx(RouteRepository $routeRepository, Request $request): Response
{
    $startLocation = $request->query->get('startLocation');
    $endLocation = $request->query->get('endLocation');
    
    if ($startLocation) {
        $routes = $routeRepository->findByStartLocation($startLocation);
    } elseif ($endLocation) {
        $routes = $routeRepository->findByEndLocation($endLocation);
    } else {
        $routes = $routeRepository->findAll();
    }

    return $this->render('adminevent/routeindex.html.twig', [
        'routes' => $routes,
    ]);
}
    #[Routing('/admin/{id}', name: 'route_showw', methods: ['GET'])]
    public function showw(\App\Entity\Route $route): Response
    {
        return $this->render('adminevent/routeshow.html.twig', [
            'route' => $route,
        ]);
    }
  

    
    #[Routing('/', name: 'route_index', methods: ['GET'])]
    public function index(RouteRepository $routeRepository, Request $request): Response
    {
        $searchType = $request->query->get('searchType');
        $searchTerm = $request->query->get('searchTerm');
        
        // If search parameters are provided, filter results
        if ($searchTerm && $searchType) {
            if ($searchType === 'startLocation') {
                $routes = $routeRepository->findByStartLocation($searchTerm);
            } elseif ($searchType === 'endLocation') {
                $routes = $routeRepository->findByEndLocation($searchTerm);
            } else {
                $routes = $routeRepository->findAll();
            }
        } else {
            $routes = $routeRepository->findAll();
        }
        
        return $this->render('route/index.html.twig', [
            'routes' => $routes,
            'searchTerm' => $searchTerm,
            'searchType' => $searchType,
        ]);
    }

    #[Routing('/new', name: 'route_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $route = new \App\Entity\Route();
        $form = $this->createForm(RouteType::class, $route);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($route);
            $entityManager->flush();

            return $this->redirectToRoute('route_index');
        }

        return $this->render('route/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Routing('/{id}', name: 'route_show', methods: ['GET'])]
    public function show(\App\Entity\Route $route): Response
    {
        return $this->render('route/show.html.twig', [
            'route' => $route,
        ]);
    }

    #[Routing('/{id}/edit', name: 'route_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, \App\Entity\Route $route, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RouteType::class, $route);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('route_index');
        }

        return $this->render('route/edit.html.twig', [
            'form' => $form->createView(),
            'route' => $route,
        ]);
    }

    #[Routing('/{id}/delete', name: 'route_delete', methods: ['POST'])]
    public function delete(Request $request, \App\Entity\Route $route, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$route->getId(), $request->request->get('_token'))) {
            $entityManager->remove($route);
            $entityManager->flush();
        }

        return $this->redirectToRoute('route_index');
    }
}
