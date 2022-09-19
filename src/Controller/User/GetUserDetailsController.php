<?php

namespace App\Controller\User;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users/{id}', name: 'app_users_details', methods: ['GET'], requirements: ['id' => '\d+'], stateless: true)]
#[OA\Get(
    path: '/api/users/{id}',
    description: "USER DETAILS BY AUTHENTICATED CLIENT",
    responses: [
        new OA\Response(response: 200, description: 'OK - id User details'),
        new OA\Response(response: 404, description: 'NOT FOUND - This user don\'t exist'),
        new OA\Response(response: 403, description: 'FORBIDDEN - This resource does not belong to you'),
        new OA\Response(response: 401, description: 'UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token'),
    ]
)]
#[OA\Parameter(
    name: 'id',
    in: 'path',
    required: true,
    description: 'The identifiant of user',
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Tag(name: 'Users')]
class GetUserDetailsController extends AbstractController
{
    public function __invoke(
        int $id,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $pool
    ): JsonResponse {

        $client = $this->getUser();

        // Cache item - return cache if item exist
        $item = 'users-details-client_' . $client->getId() . '-user_' . $id;
        if ($pool->hasItem($item)) {
            return new JsonResponse($pool->getItem($item)->get(), Response::HTTP_OK, ["cache-control" => "max-age=60"], true);
        }


        $user = $userRepository->find((int) $id);
        if ($user) {
            if ($user->getClient() !== $client) throw new HttpException(JsonResponse::HTTP_FORBIDDEN, "This resource does not belong to you");

            $content = [
                "links" => [
                    [
                        "href" => $this->generateUrl('app_users_details', ["id" => $id], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "self",
                        "method" => "GET"
                    ],
                    [
                        "href" => $this->generateUrl('app_users_delete', ["id" => $id], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "delete user",
                        "method" => "DELETE"
                    ]
                ],
                "user" => $user
            ];

            $jsonUser = $serializer->serialize($content, 'json', ['groups' => 'getUser']);
            $usersDetailsItem = $pool->getItem($item);
            $usersDetailsItem->set($jsonUser);
            $usersDetailsItem->tag(['user_' . $user->getId()]);
            $usersDetailsItem->expiresAfter(60);
            $pool->save($usersDetailsItem);
            sleep(3); // TEST CACHE WAY

            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This user don't exist");
    }
}
