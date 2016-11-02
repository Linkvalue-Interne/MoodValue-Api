<?php

namespace MoodValue\UI\Action\Responder;

use MoodValue\Infrastructure\Repository\CollectionResult;
use MoodValue\UI\Pagination\PaginatedRepresentation;
use MoodValue\UI\Pagination\ResourceCriteria;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

final class ListEvents
{
    /**
     * @var CollectionResult
     */
    private $collectionResult;

    private function __construct()
    {
    }

    public static function withResults(CollectionResult $collectionResult) : self
    {
        $self = new self();
        $self->collectionResult = $collectionResult;

        return $self;
    }

    public function __invoke(ResourceCriteria $resourceCriteria) : ResponseInterface
    {
        $pageResults = [];

        foreach ($this->collectionResult->getResults() as $event) {
            $pageResults[] = [
                'id' => $event['id'],
                'name' => $event['name'],
                'text' => $event['text'],
                'start_date' => (new \DateTimeImmutable($event['start_date']))->format(\DateTime::ISO8601),
                'end_date' => (new \DateTimeImmutable($event['end_date']))->format(\DateTime::ISO8601),
                'day_of_week' => (int)$event['day_of_week'],
                'mobile_splashscreen' => (bool)$event['mobile_splashscreen']
            ];
        }

        $paginatedRepresentation = new PaginatedRepresentation(
            $resourceCriteria, $this->collectionResult->getTotal(), $pageResults
        );

        return new JsonResponse($paginatedRepresentation->toArray());
    }
}
