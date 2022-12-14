<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Services\Hateoas;
use App\Services\CacheTools;
use OpenApi\Annotations as OA;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @OA\Get(
 *      description="USER DETAILS WITH PAGINATE FOR AUTHENTICATED CLIENT",
 *      summary="SHOW USER DETAILS"
 * )
 * @OA\Response(
 *      response=200,
 *      description="OK - id User details",
 *      @OA\JsonContent(
 *          @OA\Property(property="links", type="string",
 *              example={
 *                  {"href":"/api/users/84615", "rel":"self", "method":"GET"},
 *                  {"href":"/api/users/84615", "rel":"delete user", "method":"DELETE"}
 *              }
 *          ),
 *          @OA\Property(property="user", type="object",
 *              ref=@Model(type=User::class, groups={"getUser"})
 *          )
 *      )
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
 *      description="The identifiant of user",
 *      @OA\Schema(type="integer")
 * )
 * @OA\Tag(name="Users")
 */

#[Route('/api/users/{id}', name: 'app_users_details', methods: ['GET'], requirements: ['id' => '\d+'], stateless: true)]
class GetUserDetailsController extends AbstractController
{
    public function __invoke(
        int $id,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        Hateoas $hateoas,
        CacheTools $cacheTools,
    ): JsonResponse {

        $client = $this->getUser();

        // Cache item - return cache if item exist
        $item = 'users-details-client_' . $client->getId() . '-user_' . $id;
        if ($cacheTools->setItem($item)) {
            return new JsonResponse($cacheTools->getItem(), Response::HTTP_OK, ["cache-control" => "max-age=60"], true);
        }

        $user = $userRepository->find((int) $id);
        if ($user) {
            $this->denyAccessUnlessGranted('VIEW_USER', $user);

            $content = [
                "links" => [
                    $hateoas->createLink('app_users_details', 'GET', 'self', ["id" => $id]),
                    $hateoas->createLink('app_users_details', 'DELETE', 'delete_user', ["id" => $id])
                ],
                "user" => $user
            ];

            $jsonUser = $serializer->serialize($content, 'json', ['groups' => 'getUser']);
            $cacheTools->saveItem('user_' . $user->getId(), $jsonUser);

            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This user don't exist");
    }
}
