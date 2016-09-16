<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return new JsonResponse([
            'get_users' => $this->get('router')->generate('get_users', [], Router::ABSOLUTE_URL),
            'get_events' => $this->get('router')->generate('get_events', [], Router::ABSOLUTE_URL),
            'get_events_for_user' => $this->get('router')->generate('get_events_for_user', ['userId' => 'USER_ID'], Router::ABSOLUTE_URL),
        ]);
    }
}
