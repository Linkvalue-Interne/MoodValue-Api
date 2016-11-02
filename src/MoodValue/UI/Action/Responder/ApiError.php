<?php

namespace MoodValue\UI\Action\Responder;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

final class ApiError
{
    public function __invoke(string $message, $code) : ResponseInterface
    {
        return new JsonResponse([
            'message' => $message
        ], $code);
    }
}
