<?php

namespace App\Controller;

use App\Dto\ChangeTaskStatusInput;
use App\Dto\CreateTaskInput;
use App\Entity\Task;
use App\Http\JsonPayload;
use App\Repository\StatusRepository;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks')]
#[AsController]
final class TaskController
{
    #[Route('', methods: ['GET'])]
    public function index(Request $request, TaskRepository $tasks, StatusRepository $statuses): JsonResponse
    {
        $statusName = $request->query->all()['status'] ?? null;
        if ($statusName !== null && !is_string($statusName)) {
            return new JsonResponse(
                ['errors' => ['status' => ['This value should be of type string.']]],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        if ($statusName !== null) {
            if ($statuses->findOneBy(['name' => $statusName]) === null) {
                return new JsonResponse(['error' => 'Status not found'], JsonResponse::HTTP_NOT_FOUND);
            }
            $results = $tasks->findByStatusName($statusName);
        } else {
            $results = $tasks->findBy([], ['id' => 'ASC']);
        }

        return new JsonResponse(array_map(self::serialize(...), $results));
    }

    #[Route('/{id<\\d+>}', methods: ['GET'])]
    public function show(int $id, TaskRepository $tasks): JsonResponse
    {
        $task = $tasks->find($id);
        if ($task === null) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(self::serialize($task));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, TaskService $service): JsonResponse
    {
        $data = JsonPayload::decode($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $input = new CreateTaskInput($data['title'] ?? null, $data['description'] ?? null);
        $violations = $validator->validate($input);
        if (count($violations) > 0) {
            return JsonPayload::validationErrors($violations);
        }

        $task = $service->create($input->title, $input->description);
        if ($task === null) {
            return new JsonResponse(['error' => 'Default status not found'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(self::serialize($task), JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id<\\d+>}/status', methods: ['PATCH'])]
    public function changeStatus(int $id, Request $request, ValidatorInterface $validator, TaskRepository $tasks, TaskService $service): JsonResponse
    {
        $task = $tasks->find($id);
        if ($task === null) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = JsonPayload::decode($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $input = new ChangeTaskStatusInput($data['status'] ?? null);
        $violations = $validator->validate($input);
        if (count($violations) > 0) {
            return JsonPayload::validationErrors($violations);
        }

        if (!$service->changeStatus($task, $input->status)) {
            return new JsonResponse(['error' => 'Status not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(self::serialize($task));
    }

    #[Route('/{id<\\d+>}', methods: ['DELETE'])]
    public function delete(int $id, TaskRepository $tasks, TaskService $service): Response
    {
        $task = $tasks->find($id);
        if ($task === null) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $service->delete($task);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    private static function serialize(Task $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->getName(),
            'createdAt' => $task->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $task->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
