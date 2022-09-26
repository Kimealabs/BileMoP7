<?php

namespace App\Controller\User;

use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Repository\UserRepository;
use App\Services\CacheTools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @OA\Delete(
 *      summary="DELETE USER",
 *      description="Delete a user belonging to a authenticated client.",
 *      operationId="deleteUser"
 * ) 
 * @OA\Response(
 *      response=204,
 *      description="USER DELETED"
 * )
 * @OA\Response(
 *      response=401,
 *      description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token",
 *      @OA\JsonContent(
 *        @OA\Property(property="code", type="string", example="code: 401"),
 *        @OA\Property(property="message", type="string", example="JWT Token not found | Expired JWT Token | Invalid JWT Token")
 *      )
 * )
 * @OA\Response(
 *      response=404,
 *      description="NOT FOUND - This user don't exist",
 *      @OA\JsonContent(
 *        @OA\Property(property="code", type="string", example="404"),
 *        @OA\Property(property="message", type="string", example="This user don't exist")
 *      )
 * )
 * @OA\Response(
 *     response=403,
 *     description="FORBIDDEN - This resource does not belong to you",
 *     @OA\JsonContent(
 *       @OA\Property(property="code", type="string", example="403"),
 *       @OA\Property(property="message", type="string", example="Access denied")
 *     )
 * )
 * @OA\Parameter(
 *      name="id",
 *      in="path",
 *      required= true,
 *      description="The identifiant of user",
 *      @OA\Schema(type="integer")
 * )
 * @OA\Tag(name="Users")
 */
#[Route('/api/users/{id}', name: 'app_users_delete', methods: ['DELETE'], requirements: ['id' => '\d+'], stateless: true)]
class DeleteUserController extends AbstractController
{
    public function __invoke(
        int $id,
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        CacheTools $cacheTools
    ): JsonResponse {

        $user = $userRepository->find($id);
        if ($user) {
            $this->denyAccessUnlessGranted('DELETE_USER', $user);
            // DELETE CACHE TAG userslist (pagination and list are changed)
            // DELETE THIS USER if IN CACHE
            $cacheTools->deleteTags(["usersList", "user_" . $user->getId()]);

            $entityManagerInterface->remove($user);
            $entityManagerInterface->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This User don't exist");
    }
}
