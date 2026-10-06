<?php

namespace App\Controller;

use App\Dto\CreateStatusInput;
use App\Entity\Status;
use App\Http\JsonPayload;
use App\Repository\StatusRepository;
use App\Service\StatusService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/statuses')]
#[AsController]
final class StatusController
{
    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/statuses',
        summary: 'List statuses.',
        responses: [new OA\Response(response: 200, description: 'Statuses returned.', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Status')))],
    )]
    public function index(StatusRepository $statuses): JsonResponse
    {
        return new JsonResponse(array_map(self::serialize(...), $statuses->findBy([], ['id' => 'ASC'])));
    }

    #[Route('/{id<\\d+>}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/statuses/{id}',
        summary: 'Get a status by ID.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Status returned.', content: new OA\JsonContent(ref: '#/components/schemas/Status')),
            new OA\Response(response: 404, description: 'Status not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ],
    )]
    public function show(int $id, StatusRepository $statuses): JsonResponse
    {
        $status = $statuses->find($id);
        if ($status === null) {
            return new JsonResponse(['error' => 'Status not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(self::serialize($status));
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/statuses',
        summary: 'Create a status.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'title'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 50, pattern: '^[a-z][a-z0-9_]*$'),
                new OA\Property(property: 'title', type: 'string', maxLength: 100),
            ],
            type: 'object',
        )),
        responses: [
            new OA\Response(response: 201, description: 'Status created.', content: new OA\JsonContent(ref: '#/components/schemas/Status')),
            new OA\Response(response: 400, description: 'Malformed JSON.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 409, description: 'A status with this name already exists.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 422, description: 'Invalid status fields or a non-object JSON body.', content: new OA\JsonContent(oneOf: [new OA\Schema(ref: '#/components/schemas/ApiError'), new OA\Schema(ref: '#/components/schemas/ValidationError')])),
        ],
    )]
    public function create(Request $request, ValidatorInterface $validator, StatusService $service): JsonResponse
    {
        $data = JsonPayload::decode($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $input = new CreateStatusInput($data['name'] ?? null, $data['title'] ?? null);
        $violations = $validator->validate($input);
        if (count($violations) > 0) {
            return JsonPayload::validationErrors($violations);
        }

        $status = $service->create($input->name, $input->title);
        if ($status === null) {
            return new JsonResponse(['error' => 'Status name already exists'], JsonResponse::HTTP_CONFLICT);
        }

        $response = new JsonResponse(self::serialize($status), JsonResponse::HTTP_CREATED);
        $response->headers->set('Location', '/api/statuses/'.$status->getId());

        return $response;
    }

    #[Route('/{id<\\d+>}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/statuses/{id}',
        summary: 'Delete an unused status.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Status deleted.'),
            new OA\Response(response: 404, description: 'Status not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 409, description: 'The default new status or a status used by tasks cannot be deleted.', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ],
    )]
    public function delete(int $id, StatusRepository $statuses, StatusService $service): Response
    {
        $status = $statuses->find($id);
        if ($status === null) {
            return new JsonResponse(['error' => 'Status not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$service->delete($status)) {
            $error = $status->getName() === 'new'
                ? 'Default status cannot be deleted'
                : 'Status is used by tasks';
            return new JsonResponse(['error' => $error], JsonResponse::HTTP_CONFLICT);
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    private static function serialize(Status $status): array
    {
        return ['id' => $status->getId(), 'name' => $status->getName(), 'title' => $status->getTitle()];
    }
}
