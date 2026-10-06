<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class JsonPayload
{
    public static function decode(Request $request): array|JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new JsonResponse(['error' => 'Malformed JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!$data instanceof \stdClass) {
            return new JsonResponse(['error' => 'JSON body must be an object'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return get_object_vars($data);
    }

    public static function validationErrors(ConstraintViolationListInterface $violations): JsonResponse
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }
}
