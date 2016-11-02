<?php

namespace MoodValue\UI\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zend\Diactoros\Response\JsonResponse;

final class Index
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(ServerRequestInterface $request) : ResponseInterface
    {
        return new JsonResponse([
            'get_users' => $this->urlGenerator->generate(
                'moodvalue_list_users', [], UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'get_events' => $this->urlGenerator->generate(
                'moodvalue_list_events', [], UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'get_events_for_user' => $this->urlGenerator->generate(
                'moodvalue_list_events_for_user', ['user' => 'USER_ID'], UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }
}
