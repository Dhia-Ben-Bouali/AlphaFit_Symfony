<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommandeController extends AbstractController {
    #[Route('/commande', name: 'app_commande')]
public function index(Request $request, CommandeRepository $commandeRepository, ChartBuilderInterface $chartBuilder): Response {
    $firstName = $request->query->get('cardholderName');
    $commandes = $firstName ? $commandeRepository->findByFirstName($firstName) : $commandeRepository->findAll();
    $commandes = $commandeRepository->findAll(); // Assuming findAll() fetches all orders

    $data = [];
    foreach ($commandes as $commande) {
        $date = $commande->getDate()->format('Y-m-d'); // Assuming your Date is a DateTime object
        if (!isset($data[$date])) {
            $data[$date] = 0;
        }
        $data[$date] += $commande->getTotale(); // Assuming getTotal() gives the total of an order
    }

    // Prepare labels and totals for the chart
    $labels = array_keys($data);
    $totals = array_values($data);

    $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
    $chart->setData([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Daily Sales',
                'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'data' => $totals,
            ],
        ],
    ]);
    $chart->setOptions([
        'responsive' => true,
        'scales' => [
            'yAxes' => [
                [
                    'ticks' => [
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ],
    ]);
    return $this->render('commande/index.html.twig', [
        'commandes' => $commandes,
        'cardholderName' => $firstName, // Pass the search term to the template
        'labels' => $labels,
        'totals' => $totals,
    ]);
}

#[Route('/commande/chart', name: 'app_commande_chart')]
public function chart(CommandeRepository $commandeRepository, ChartBuilderInterface $chartBuilder): Response {
    $commandes = $commandeRepository->findAll(); // Assuming findAll() fetches all orders

    $data = [];
    foreach ($commandes as $commande) {
        $date = $commande->getDate()->format('Y-m-d'); // Assuming your Date is a DateTime object
        if (!isset($data[$date])) {
            $data[$date] = 0;
        }
        $data[$date] += $commande->getTotale(); // Assuming getTotal() gives the total of an order
    }

    // Prepare labels and totals for the chart
    $labels = array_keys($data);
    $totals = array_values($data);

    $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
    $chart->setData([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Daily Sales',
                'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'data' => $totals,
            ],
        ],
    ]);
    $chart->setOptions([
        'responsive' => true,
        'scales' => [
            'yAxes' => [
                [
                    'ticks' => [
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ],
    ]);

    return $this->render('commande/index.html.twig', [
        'labels' => $labels,
        'totals' => $totals,
    ]);}



    


    #[Route('/commande/export', name: 'app_commande_export')]
    public function export(CommandeRepository $commandeRepository): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $salesData = $commandeRepository->findTotalOfOrdersByDate();
        
        // Set the header row
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Total Sales');

        // Fill the spreadsheet with data
        $row = 2;
        foreach ($salesData as $data) {
            $date = new \DateTime($data['date']);
            // Use the $date object for formatting
            $sheet->setCellValue('A' . $row, $date->format('Y-m-d'));
            $sheet->setCellValue('B' . $row, $data['total']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        // Create a StreamedResponse to download the file
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Set the content type and the content disposition
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="sales_data.xlsx"');

        return $response;
    }
}
