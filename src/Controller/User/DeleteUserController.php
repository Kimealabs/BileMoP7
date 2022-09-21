<?php

namespace App\Controller\User;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users/{id}', name: 'app_users_delete', methods: ['DELETE'], requirements: ['id' => '\d+'], stateless: true)]
#[OA\Get(
    path: '/api/users/{id}',
    summary: "DELETE AN USER",
    description: "DELETE USER BY id",
    responses: [
        new OA\Response(response: 204, description: 'NO CONTENT - User deleted'),
        new OA\Response(response: 404, description: 'NOT FOUND - This user don\'t exist'),
        new OA\Response(response: 403, description: 'FORBIDDEN - This resource does not belong to you'),
        new OA\Response(response: 401, description: 'UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token'),
    ]
)]
#[OA\Parameter(
    name: 'id',
    in: 'path',
    required: true,
    description: 'The idendifiant of user',
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Tag(name: 'Users')]
class DeleteUserController extends AbstractController
{
    public function __invoke(
        int $id,
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        TagAwareCacheInterface $pool
    ): JsonResponse {

        $client = $this->getUser();

        $user = $userRepository->find($id);
        if ($user) {
            if ($user->getClient() === $client) {
                // DELETE CACHE TAG userslist (pagination and list are changed)
                // DELETE THIS USER if IN CACHE
                $pool->invalidateTags(["usersList", "user_" . $user->getId()]);

                $entityManagerInterface->remove($user);
                $entityManagerInterface->flush();

                return new JsonResponse(null, Response::HTTP_NO_CONTENT);
            }
            throw new HttpException(JsonResponse::HTTP_FORBIDDEN, "This resource does not belong to you");
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This User don't exist");
    }
}
