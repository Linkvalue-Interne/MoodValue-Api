<?php

namespace MoodValue\UI\Action;

use MoodValue\Model\User\Command\AddDeviceTokenToUser;
use MoodValue\Model\User\Command\RegisterUser as RegisterUserCommand;
use MoodValue\Model\User\UserId;
use MoodValue\Projection\User\UserFinder;
use MoodValue\UI\Action\Responder\AddNewDeviceToken;
use MoodValue\UI\Action\Responder\ApiError;
use MoodValue\UI\Action\Responder\RegisterNewUser;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Exception\CommandDispatchException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RegisterUser
{
    use JsonPayloadDecoder;

    /**
     * @var UserFinder
     */
    private $userFinder;

    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(UserFinder $userFinder, CommandBus $commandBus)
    {
        $this->userFinder = $userFinder;
        $this->commandBus = $commandBus;
    }

    public function __invoke(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            $payload = $this->getPayloadFromRequest($request);
        } catch (\Throwable $error) {
            return (new ApiError)($error->getMessage(), 400);
        }

        try {
            if ($user = $this->userFinder->findOneByEmail($payload['email'] ?? '')) {
                $this->commandBus->dispatch(
                    AddDeviceTokenToUser::withData(
                        $userId = $payload['user_id'] ?? '',
                        $payload['device_token'] ?? ''
                    )
                );

                return (new RegisterNewUser)($userId);
            }

            $this->commandBus->dispatch(
                RegisterUserCommand::withData(
                    $userId = UserId::generate()->toString(),
                    $payload['email'] ?? '',
                    $payload['device_token'] ?? ''
                )
            );

            return (new AddNewDeviceToken)($userId);
        } catch (CommandDispatchException $e) {
            return (new ApiError)($e->getPrevious()->getMessage(), 400);
        } catch (\Throwable $error) {
            return (new ApiError)($error->getMessage(), 500);
        }
    }
}
