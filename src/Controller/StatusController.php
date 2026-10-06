<?php

namespace App\Controller;

use App\Dto\CreateStatusInput;
use App\Entity\Status;
use App\Http\JsonPayload;
use App\Repository\StatusRepository;
use App\Service\StatusService;
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
    public function index(StatusRepository $statuses): JsonResponse
    {
        return new JsonResponse(array_map(self::serialize(...), $statuses->findBy([], ['id' => 'ASC'])));
    }

    #[Route('/{id<\\d+>}', methods: ['GET'])]
    public function show(int $id, StatusRepository $statuses): JsonResponse
    {
        $status = $statuses->find($id);
        if ($status === null) {
            return new JsonResponse(['error' => 'Status not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(self::serialize($status));
    }

    #[Route('', methods: ['POST'])]
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

        return new JsonResponse(self::serialize($status), JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id<\\d+>}', methods: ['DELETE'])]
    public function delete(int $id, StatusRepository $statuses, StatusService $service): Response
    {
        $status = $statuses->find($id);
        if ($status === null) {
            return new JsonResponse(['error' => 'Status not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$service->delete($status)) {
            return new JsonResponse(['error' => 'Status is used by tasks'], JsonResponse::HTTP_CONFLICT);
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    private static function serialize(Status $status): array
    {
        return ['id' => $status->getId(), 'name' => $status->getName(), 'title' => $status->getTitle()];
    }
}
