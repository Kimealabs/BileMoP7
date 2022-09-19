<?php

namespace App\Controller\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users/{id}', name: 'app_users_delete', methods: ['DELETE'], requirements: ['id' => '\d+'], stateless: true)]
#[IsGranted('ROLE_USER', message: 'You do not have the necessary rights for this resource')]
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
