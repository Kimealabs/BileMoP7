<?php

namespace App\Controller\User;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users/{id}', name: 'app_user_details', methods: ['GET'], requirements: ['id' => '\d+'])]
#[IsGranted('ROLE_USER', message: 'You do not have the necessary rights for this resource')]
class GetUserDetailsController extends AbstractController
{
    public function __invoke(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $client = $this->getUser();
        $user = $userRepository->find($id);
        if ($user) {
            if ($user->getClient() !== $client) throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This resource does not belong to you");

            $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This user don't exist");
    }
}
