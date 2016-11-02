<?php

namespace MoodValue\UI\Action;

use MoodValue\UI\Pagination\PaginatedRepresentation;
use MoodValue\UI\Pagination\ResourceCriteria;
use MoodValue\Projection\User\UserFinder;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

final class ListUsers
{
    /**
     * @var UserFinder
     */
    private $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $resourceCriteria = new ResourceCriteria($request->getQueryParams());

        $usersCollectionResult = $this->userFinder->findAll(
            $resourceCriteria->getStart(), $resourceCriteria->getLimit()
        );

        $pageResults = [];

        foreach ($usersCollectionResult->getResults() as $user) {
            $pageResults[] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'device_tokens' => explode(',', $user['device_tokens']),
                'created_at' => (new \DateTimeImmutable($user['created_at']))->format(\DateTime::ISO8601)
            ];
        }

        $paginatedRepresentation = new PaginatedRepresentation($resourceCriteria, $usersCollectionResult->getTotal(), $pageResults);

        return new JsonResponse($paginatedRepresentation->toArray());
    }
}
