<?php

namespace App\Controller\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/api/users', name: 'app_users_list', methods: ['GET'])]
#[IsGranted('ROLE_USER', message: 'You do not have the necessary rights for this resource')]
class GetUsersController extends AbstractController
{
    public function __invoke(SerializerInterface $serializer): JsonResponse
    {
        $client = $this->getUser();

        $users = $client->getUser();
        if ($users) {
            $jsonUsersList = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);
            return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client have not user");
    }
}
