<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Données reçues du client pour créer ou modifier une tâche.
 * Séparer l'entrée de l'entité permet de valider proprement et de
 * ne jamais exposer la structure interne de la base.
 */
class TaskInput
{
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le titre doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    public ?string $title = null;

    #[Assert\Length(max: 2000, maxMessage: 'La description est trop longue.')]
    public ?string $description = null;

    public ?bool $done = null;

    #[Assert\Range(
        min: 1,
        max: 3,
        notInRangeMessage: 'La priorité doit être comprise entre {{ min }} et {{ max }}.'
    )]
    public ?int $priority = null;
}
