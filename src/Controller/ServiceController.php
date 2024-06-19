<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use TCPDF;

class ServiceController extends AbstractController
{
    #[Route('/service', name: 'app_service')]
    public function index(): Response
    {
        return $this->render('service/index.html.twig', [
            'controller_name' => 'ServiceController',
        ]);
    }
    #[Route('/service/pdf', name: 'generate_pdf')]
    public function generatePdf(): Response
    {
        // Fetch services from the database or any other source
        $services = $this->getDoctrine()->getRepository(Service::class)->findAll();

        // Create a new PDF instance
        $pdf = new TCPDF();

        // Set PDF properties
        $pdf->SetCreator('Alpha Fit');
        $pdf->SetAuthor('Administration');
        $pdf->SetTitle('Services PDF');

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('times', '', 12);

        // Add title
        $pdf->Cell(0, 10, 'List of Services', 0, 1, 'C');

        // Add line break
        $pdf->Ln(10);

        // Create headers for the table
        $pdf->Cell(60, 10, 'Service Name', 1);
        $pdf->Cell(0, 10, 'Description', 1);
        $pdf->Ln();

        // Iterate through services and add them to the table
        foreach ($services as $service) {
            $pdf->Cell(60, 10, $service->getNom(), 1);
            $pdf->MultiCell(0, 10, $service->getDescription(), 1);
        }

        // Output the PDF to the browser
        $response = new Response($pdf->Output('services.pdf', 'I'));

        // Set response headers
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
    #[Route("/service/addService", name:"add_service", methods:["GET","POST"])]
    public function addService(Request $request): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($service);
            $entityManager->flush();

            return $this->redirectToRoute('show_all_services');
        }

        return $this->render('service/new.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/service/get-service-details/{id}', name: 'get_service_details')]
    public function getServiceDetails(Request $request, $id): JsonResponse
    {
        // You should implement your logic to fetch user details based on the $id parameter
        // Replace this with your actual logic to retrieve user details from the database or any other source
        $serviceDetails = $this->getDoctrine()->getRepository(Service::class)->find($id);

        // Check if user details were found
        if (!$serviceDetails) {
            return new JsonResponse(['error' => 'Service not found'], 404);
        }

        // Return user details in JSON format
        return new JsonResponse([
            'nom' => $serviceDetails->getNom(),
            'description' => $serviceDetails->getDescription(),
            
        ]);
    }    
    #[Route("/service/service{id}", name: "show_service", methods: ["GET"])]
    public function showService(int $id, ServiceRepository $serviceRepository): Response
    {
        // Fetch the Service entity from the repository based on the $id
        
        $service = $serviceRepository->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }
    #[Route("/service/edit/{id}", name:"edit_service", methods:["GET", "POST"])]
    public function editService(int $id, Request $request, EntityManagerInterface $entityManager, ServiceRepository $packRepository): Response
    {
        $service = $entityManager->getRepository(Service::class)->find($id);
        $packs = $this->getDoctrine()->getRepository(Service::class)->findAll();

        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        // Manually set user fields from the request
        $service->setNom($request->request->get('service')['nom']);
        $service->setDescription($request->request->get('service')['description']);

        $entityManager->persist($service);
        $entityManager->flush();

        $this->addFlash('success', 'Service updated successfully.');
        return $this->redirectToRoute('show_all_services');
    }
    #[Route("/service/delete/{id}", name:"delete_service", methods:["POST"])]
    public function deleteService(int $id, EntityManagerInterface $entityManager): Response
    {
        $service = $entityManager->getRepository(Service::class)->find($id);

        if (!$service) {
            // Handle the case where the user with the given ID is not found
            throw $this->createNotFoundException('Service not found');
        }

        // Ensure the user is authenticated or add additional checks if needed
        // (e.g., check if the user has the right to delete users)
        $entityManager->remove($service);
        $entityManager->flush();

        $this->addFlash('success', 'Service deleted successfully.');

        return $this->redirectToRoute('show_all_services');
    }
    #[Route("/service/showall", name:"show_all_services", methods:["GET"])]
    public function showAllServices(ServiceRepository $serviceRepository, Request $request): Response
    {
        $services = $serviceRepository->findAll();
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($service);
            $entityManager->flush();

            return $this->redirectToRoute('show_all_services');
        }
        return $this->render('service/show_all.html.twig', [
            'services' => $services,
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }
}
