<?php

namespace MoodValue\UI\Action;

use MoodValue\Projection\Event\EventFinder;
use MoodValue\UI\Pagination\ResourceCriteria;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ListEventsForUser
{
    /**
     * @var EventFinder
     */
    private $eventFinder;

    public function __construct(EventFinder $eventFinder)
    {
        $this->eventFinder = $eventFinder;
    }

    public function __invoke(ServerRequestInterface $request) : ResponseInterface
    {
        $resourceCriteria = new ResourceCriteria($request->getQueryParams());

        $eventsCollectionResult = $this->eventFinder->findAllForUser(
            $request->getAttribute('user'), $resourceCriteria->getStart(), $resourceCriteria->getLimit()
        );

        return (Responder\ListEvents::withResults($eventsCollectionResult))($resourceCriteria);
    }
}
