<?php

namespace MoodValue\UI\Action;

use Psr\Http\Message\ServerRequestInterface;

trait JsonPayloadDecoder
{
    private function getPayloadFromRequest(ServerRequestInterface $request) : array
    {
        $payload = json_decode($request->getBody(), true);

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                throw new \Exception('Invalid JSON, maximum stack depth exceeded.', 400);
            case JSON_ERROR_UTF8:
                throw new \Exception('Malformed UTF-8 characters, possibly incorrectly encoded.', 400);
            case JSON_ERROR_SYNTAX:
            case JSON_ERROR_CTRL_CHAR:
            case JSON_ERROR_STATE_MISMATCH:
                throw new \Exception('Invalid JSON.', 400);
        }

        return $payload === null ? [] : $payload;
    }
}
