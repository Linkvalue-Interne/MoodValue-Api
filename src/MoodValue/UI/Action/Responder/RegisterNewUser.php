<?php

namespace MoodValue\UI\Action\Responder;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

final class RegisterNewUser
{
    public function __invoke(string $userId) : ResponseInterface
    {
        return new JsonResponse([
            'id' => $userId
        ], 201);
    }
}
