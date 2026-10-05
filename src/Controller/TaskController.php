<?php

namespace App\Controller;

use App\Dto\TaskInput;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TaskRepository $tasks,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'task_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $criteria = [];
        if (null !== $request->query->get('done')) {
            $criteria['done'] = $request->query->getBoolean('done');
        }

        $tasks = $this->tasks->findBy($criteria, ['createdAt' => 'DESC']);

        return $this->json($tasks, Response::HTTP_OK, [], ['groups' => 'task:read']);
    }

    #[Route('/{id}', name: 'task_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $task = $this->tasks->find($id);
        if (null === $task) {
            return $this->notFound($id);
        }

        return $this->json($task, Response::HTTP_OK, [], ['groups' => 'task:read']);
    }

    #[Route('', name: 'task_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $input = $this->readInput($request);
        if ($input instanceof JsonResponse) {
            return $input;
        }

        $errors = $this->validator->validate($input);
        if (count($errors) > 0) {
            return $this->validationError($errors);
        }

        $task = (new Task())
            ->setTitle($input->title)
            ->setDescription($input->description)
            ->setDone($input->done ?? false)
            ->setPriority($input->priority ?? 2);

        $this->em->persist($task);
        $this->em->flush();

        return $this->json($task, Response::HTTP_CREATED, [], ['groups' => 'task:read']);
    }

    #[Route('/{id}', name: 'task_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $task = $this->tasks->find($id);
        if (null === $task) {
            return $this->notFound($id);
        }

        $input = $this->readInput($request);
        if ($input instanceof JsonResponse) {
            return $input;
        }

        $errors = $this->validator->validate($input);
        if (count($errors) > 0) {
            return $this->validationError($errors);
        }

        $task->setTitle($input->title)
            ->setDescription($input->description);
        if (null !== $input->done) {
            $task->setDone($input->done);
        }
        if (null !== $input->priority) {
            $task->setPriority($input->priority);
        }

        $this->em->flush();

        return $this->json($task, Response::HTTP_OK, [], ['groups' => 'task:read']);
    }

    #[Route('/{id}/toggle', name: 'task_toggle', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function toggle(int $id): JsonResponse
    {
        $task = $this->tasks->find($id);
        if (null === $task) {
            return $this->notFound($id);
        }

        $task->toggle();
        $this->em->flush();

        return $this->json($task, Response::HTTP_OK, [], ['groups' => 'task:read']);
    }

    #[Route('/{id}', name: 'task_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $task = $this->tasks->find($id);
        if (null === $task) {
            return $this->notFound($id);
        }

        $this->em->remove($task);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Désérialise le corps JSON en TaskInput, ou renvoie une réponse 400 si le JSON est invalide.
     */
    private function readInput(Request $request): TaskInput|JsonResponse
    {
        try {
            return $this->serializer->deserialize(
                $request->getContent() ?: '{}',
                TaskInput::class,
                'json'
            );
        } catch (SerializerException) {
            return $this->json(['error' => 'Corps JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validationError(ConstraintViolationListInterface $errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()][] = $error->getMessage();
        }

        return $this->json(['errors' => $messages], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function notFound(int $id): JsonResponse
    {
        return $this->json(['error' => sprintf('Tâche %d introuvable.', $id)], Response::HTTP_NOT_FOUND);
    }
}
