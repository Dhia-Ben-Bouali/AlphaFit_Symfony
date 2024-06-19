<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalorieCalculatorController extends AbstractController
{
    #[Route('/caloriecalculator', name: 'app_calorie_calculator')]
    public function index(Request $request): Response
    {
        // Handle form submission
        if ($request->isMethod('POST')) {
            // Retrieve user input
            $weight = $request->request->get('weight');
            $height = $request->request->get('height');
            $age = $request->request->get('age');
            $gender = $request->request->get('gender');

            // Calculate total calories based on user input and gender
            $totalCalories = $this->calculateTotalCalories($weight, $height, $age, $gender);

            // Pass the total calories to the template
            return $this->render('calorie_calculator/index.html.twig', [
                'totalCalories' => $totalCalories,
            ]);
        }

        // Render the initial form
        return $this->render('calorie_calculator/index.html.twig');
    }
    /**
     * Calculate total calories based on user input and gender.
     *
     * @param int $weight
     * @param int $height
     * @param int $age
     * @param string $gender
     * @return int
     */
    private function calculateTotalCalories($weight, $height, $age, $gender): int
    {
        // Use the provided equations to calculate total calories
        if ($gender === 'male') {
            $totalCalories = 10 * $weight + 6.25 * $height - 5 * $age + 5;
        } elseif ($gender === 'female') {
            $totalCalories = 10 * $weight + 6.25 * $height - 5 * $age - 161;
        } else {
            // Handle other gender options if needed
            $totalCalories = 0;
        }

        return (int)$totalCalories;
    }
}
