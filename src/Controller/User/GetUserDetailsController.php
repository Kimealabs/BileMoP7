<?php

namespace App\Controller\User;

use App\Entity\User;
use OpenApi\Annotations as OA;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
 *     description="FORBIDDEN - This resource does not belong to you'",
 *     @OA\JsonContent(
 *       @OA\Property(property="code", type="string", example="403"),
 *       @OA\Property(property="message", type="string", example="This resource does not belong to you")
 *     )
 * )

 * @OA\Parameter(
 *      name="id",
 *      in="path",
 *      description="The identifiant of user",
 *      @OA\Schema(type="int")
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
