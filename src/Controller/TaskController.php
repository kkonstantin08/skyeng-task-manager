<?php

namespace App\Controller;

use App\Dto\ChangeTaskStatusInput;
use App\Dto\CreateTaskInput;
use App\Entity\Task;
use App\Http\JsonPayload;
use App\Repository\StatusRepository;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use OpenApi\Attributes as OA;
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
    #[OA\Get(
        path: '/api/tasks',
        summary: 'List tasks, optionally filtered by status name.',
        parameters: [new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Tasks returned.', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Task'))),
            new OA\Response(response: 404, description: 'The requested status does not exist.', content: new OA\JsonContent(properties: [new OA\Property(property: 'error', type: 'string', example: 'Status not found')], type: 'object')),
            new OA\Response(response: 422, description: 'The status query parameter must be a string.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
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
    #[OA\Get(
        path: '/api/tasks/{id}',
        summary: 'Get a task by ID.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Task returned.', content: new OA\JsonContent(ref: '#/components/schemas/Task')),
            new OA\Response(response: 404, description: 'Task not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ],
    )]
    public function show(int $id, TaskRepository $tasks): JsonResponse
    {
        $task = $tasks->find($id);
        if ($task === null) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(self::serialize($task));
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/tasks',
        summary: 'Create a task with the default new status.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['title'],
            properties: [
                new OA\Property(property: 'title', type: 'string', maxLength: 255),
                new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 5000),
            ],
            type: 'object',
        )),
        responses: [
            new OA\Response(response: 201, description: 'Task created.', content: new OA\JsonContent(ref: '#/components/schemas/Task')),
            new OA\Response(response: 400, description: 'Malformed JSON.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 422, description: 'Invalid task fields or a non-object JSON body.', content: new OA\JsonContent(oneOf: [new OA\Schema(ref: '#/components/schemas/ApiError'), new OA\Schema(ref: '#/components/schemas/ValidationError')])),
            new OA\Response(response: 500, description: 'The default new status is unavailable.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ],
    )]
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

        $response = new JsonResponse(self::serialize($task), JsonResponse::HTTP_CREATED);
        $response->headers->set('Location', '/api/tasks/'.$task->getId());

        return $response;
    }

    #[Route('/{id<\\d+>}/status', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/tasks/{id}/status',
        summary: 'Change a task status.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['status'], properties: [new OA\Property(property: 'status', type: 'string', maxLength: 50, pattern: '^[a-z][a-z0-9_]*$')], type: 'object')),
        responses: [
            new OA\Response(response: 200, description: 'Task status changed.', content: new OA\JsonContent(ref: '#/components/schemas/Task')),
            new OA\Response(response: 400, description: 'Malformed JSON.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 404, description: 'Task or requested status not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 422, description: 'Invalid status value or a non-object JSON body.', content: new OA\JsonContent(oneOf: [new OA\Schema(ref: '#/components/schemas/ApiError'), new OA\Schema(ref: '#/components/schemas/ValidationError')])),
        ],
    )]
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
    #[OA\Delete(
        path: '/api/tasks/{id}',
        summary: 'Delete a task.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Task deleted.'),
            new OA\Response(response: 404, description: 'Task not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ],
    )]
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
            'created_at' => $task->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $task->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
