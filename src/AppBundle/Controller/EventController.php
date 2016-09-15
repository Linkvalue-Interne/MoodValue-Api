<?php

namespace AppBundle\Controller;

use Ramsey\Uuid\Uuid;
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
        return new JsonResponse([
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Contrib',
                'text' => 'Comment était la contrib?',
                'date' => (new \DateTimeImmutable())->format(\DateTime::ISO8601),
                'mobile_splashscreen' => true,
            ]
        ]);
    }

    /**
     * @Route("/users/{userId}/events", name="get_events_for_user")
     * @Method({"GET"})
     */
    public function getEventsForUserAction(Request $request, $userId)
    {
        return new JsonResponse([
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Contrib',
                'text' => 'Comment était la contrib?',
                'date' => (new \DateTimeImmutable())->format(\DateTime::ISO8601),
                'mobile_splashscreen' => true,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'Inauguration',
                'text' => 'Comment était la soirée d\'inauguration?',
                'date' => (new \DateTimeImmutable())->format(\DateTime::ISO8601),
                'mobile_splashscreen' => false,
            ]
        ]);
    }
}
