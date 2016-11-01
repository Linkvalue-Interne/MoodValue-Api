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
        $resourceCriteria = new ResourceCriteria($request->query->all());

        $usersCollectionResult = $this->get('moodvalue.moodvalue_projection.user_finder')->findAll(
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

    /**
     * @Route("", name="create_user")
     * @Method({"PUT"})
     */
    public function createUserAction(Request $request)
    {
        // @todo
//        $body = json_decode($request->getContent(), true);
//        $userEmail = EmailAddress::fromString($body['email']);
//        $userRepository = $this->get('moodvalue.moodvalue_projection.user_finder');
//        $userEmailAlreadyExists = $userRepository->emailExists($userEmail);
//
//        $user = User::registerWithData(
//            UserId::generate(),
//            $userEmail,
//            DeviceToken::fromString($body['device_token'])
//        );
//
//        if (!$userEmailAlreadyExists) {
//            $userRepository->add($user);
//        } else {
//            // Add deviceToken to user if it's a new one
//            $userRepository->addDeviceToken($user->getUserId(), $user->getDeviceToken());
//        }
//
//        return new JsonResponse([
//            'id' => $user->getUserId()->toString(),
//            'email' => $user->getEmailAddress()->toString()
//        ], $userEmailAlreadyExists ? Response::HTTP_OK : Response::HTTP_CREATED);
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
