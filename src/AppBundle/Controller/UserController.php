<?php

namespace AppBundle\Controller;

use AppBundle\Pagination\PaginatedRepresentation;
use AppBundle\Pagination\ResourceCriteria;
use MoodValue\Model\User\DeviceToken;
use MoodValue\Model\User\EmailAddress;
use MoodValue\Model\User\User;
use MoodValue\Model\User\UserId;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/users")
 */
class UserController extends Controller
{
    /**
     * @Route("", name="get_users")
     * @Method({"GET"})
     */
    public function getUsersAction(Request $request)
    {
        $users = $this->get('user.repository')->findAll();

        $pageResults = [];

        array_walk($users, function ($user) use (&$pageResults) {
            $pageResults[] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'device_tokens' => explode(',', $user['device_tokens'])
            ];
        });

        $paginatedRepresentation = new PaginatedRepresentation(1, 100, 10, $pageResults);

        return new JsonResponse($paginatedRepresentation->toArray());
    }

    /**
     * @Route("", name="create_user")
     * @Method({"PUT"})
     */
    public function createUserAction(Request $request)
    {
        $body = json_decode($request->getContent(), true);
        $userEmail = EmailAddress::fromString($body['email']);
        $userEmailAlreadyExists = $this->get('user.repository')->emailExists($userEmail);

        $user = User::create(
            UserId::generate(),
            $userEmail,
            DeviceToken::fromString($body['device_token'])
        );

        if (!$userEmailAlreadyExists) {
            $this->get('user.repository')->add($user);
        }

        return new JsonResponse([
            'id' => $user->getUserId()->toString(),
            'email' => $user->getEmailAddress()->toString()
        ], $userEmailAlreadyExists ? Response::HTTP_OK : Response::HTTP_CREATED);
    }

    /**
     * @Route("/{userId}", name="delete_user")
     * @Method({"DELETE"})
     */
    public function deleteUserAction(Request $request, $userId)
    {
        $userId = UserId::fromString($userId);

        // @TODO

        return new JsonResponse([
            'message' => sprintf('User with user id %s has been deleted', $userId->toString())
        ], Response::HTTP_NO_CONTENT);
    }
}
