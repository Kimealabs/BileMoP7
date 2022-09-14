<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    #[Route('/api/clients/{id}/users', name: 'app_users', methods: ['GET'])]
    public function usersListByClient(int $id, ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $client = $clientRepository->find($id);
        if (!$client) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client don't exist");
        }

        $users = $client->getUsers();
        if ($users) {
            $jsonUsersList = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);
            return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client have not user");
    }




    #[Route('/api/clients/{id}/users/{userId}', name: 'app_user', methods: ['GET'])]
    public function userDetailsByClient(int $id, int $userId, ClientRepository $clientRepository, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $client = $clientRepository->find($id);
        if (!$client) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client don't exist");
        }

        $user = $userRepository->find($userId);
        if ($user) {
            $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This user don't exist");
    }




    #[Route('/api/clients/{id}/users', name: 'app_new_user', methods: ['POST'])]
    public function newUserByClient(
        int $id,
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManagerInterface,
        ClientRepository $clientRepository,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {

        $client = $clientRepository->find($id);
        if (!$client) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client don't exist");
        }

        $post = $request->getContent();

        $user = $serializer->deserialize($post, User::class, 'json');
        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, $errors[0]->getMessage());
        }

        $user->setClient($client);
        $entityManagerInterface->persist($user);
        $entityManagerInterface->flush();

        $postJson = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
        $location = $urlGenerator->generate('app_user', ['id' => $client->getId(), 'userId' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($postJson, Response::HTTP_CREATED, ["Location" => $location], true);
    }



    #[Route('/api/clients/{id}/users/{userId}', name: 'app_delete_user', methods: ['DELETE'])]
    public function deleteUserByClient(
        int $id,
        int $userId,
        EntityManagerInterface $entityManagerInterface,
        ClientRepository $clientRepository,
        UserRepository $userRepository,
    ): JsonResponse {

        $client = $clientRepository->find($id);
        if (!$client) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client don't exist");
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This User don't exist");
        }

        $entityManagerInterface->remove($user);
        $entityManagerInterface->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
