<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'name' => 'Task API',
            'description' => 'Petite API REST de gestion de tâches (Symfony, démonstration).',
            'author' => 'Boubacar Konaté',
            'endpoints' => [
                'GET /api/tasks' => 'Lister les tâches (filtre ?done=0|1)',
                'GET /api/tasks/{id}' => 'Voir une tâche',
                'POST /api/tasks' => 'Créer une tâche',
                'PUT /api/tasks/{id}' => 'Modifier une tâche',
                'PATCH /api/tasks/{id}/toggle' => 'Basculer fait / à faire',
                'DELETE /api/tasks/{id}' => 'Supprimer une tâche',
            ],
        ]);
    }
}
