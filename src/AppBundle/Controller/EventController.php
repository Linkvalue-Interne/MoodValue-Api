<?php

namespace AppBundle\Controller;

use AppBundle\Pagination\PaginatedRepresentation;
use AppBundle\Pagination\ResourceCriteria;
use MoodValue\Model\User\UserId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    /**
     * @Route("/events", name="get_events")
     * @Method({"GET"})
     */
    public function getEventsAction(Request $request)
    {
        $resourceCriteria = new ResourceCriteria($request->query->all());

        $eventsCollectionResult = $this->get('moodvalue.moodvalue_projection.event_finder')->findAll(
            $resourceCriteria->getStart(), $resourceCriteria->getLimit()
        );

        $pageResults = [];

        foreach ($eventsCollectionResult->getResults() as $event) {
            $pageResults[] = [
                'id' => $event['id'],
                'name' => $event['name'],
                'text' => $event['text'],
                'start_date' => (new \DateTimeImmutable($event['start_date']))->format(\DateTime::ISO8601),
                'end_date' => (new \DateTimeImmutable($event['end_date']))->format(\DateTime::ISO8601),
                'day_of_week' => (int) $event['day_of_week'],
                'mobile_splashscreen' => (bool) $event['mobile_splashscreen']
            ];
        }

        $paginatedRepresentation = new PaginatedRepresentation($resourceCriteria, $eventsCollectionResult->getTotal(), $pageResults);

        return new JsonResponse($paginatedRepresentation->toArray());
    }

    /**
     * @Route("/users/{userId}/events", name="get_events_for_user")
     * @Method({"GET"})
     */
    public function getEventsForUserAction(Request $request, $userId)
    {
        $resourceCriteria = new ResourceCriteria($request->query->all());

        $eventsCollectionResult = $this->get('moodvalue.moodvalue_projection.event_finder')->findAllForUser(
            $userId, $resourceCriteria->getStart(), $resourceCriteria->getLimit()
        );

        $pageResults = [];

        foreach ($eventsCollectionResult->getResults() as $event) {
            $pageResults[] = [
                'id' => $event['id'],
                'name' => $event['name'],
                'text' => $event['text'],
                'start_date' => (new \DateTimeImmutable($event['start_date']))->format(\DateTime::ISO8601),
                'end_date' => (new \DateTimeImmutable($event['end_date']))->format(\DateTime::ISO8601),
                'day_of_week' => (int) $event['day_of_week'],
                'mobile_splashscreen' => (bool) $event['mobile_splashscreen']
            ];
        }

        $paginatedRepresentation = new PaginatedRepresentation($resourceCriteria, $eventsCollectionResult->getTotal(), $pageResults);

        return new JsonResponse($paginatedRepresentation->toArray());
    }
}
