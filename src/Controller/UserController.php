<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    #[Route('/api/clients/{id}/users', name: 'app_users', methods: ['GET'])]
    public function usersListByClient(int $id, ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $client = $clientRepository->find($id);
        $users = $client->getUsers();
        if ($users) {
            $jsonUsersList = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);
            return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/clients/{id}/users/{userId}', name: 'app_user', methods: ['GET'])]
    public function userDetailsByClient(int $userId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($userId);
        if ($user) {
            $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
