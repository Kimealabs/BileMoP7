<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Services\Hateoas;
use App\Services\Paginator;
use OpenApi\Annotations as OA;
use App\Services\PaginateHateoas;
use App\Repository\UserRepository;
use App\Services\CacheTools;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @OA\Get(
 *      description="USERS LIST PAGINATE FOR AUTHENTICATED CLIENT",
 *      summary="LIST ALL USERS FOR CLIENT"
 * )
 * @OA\Response(
 *      response=200,
 *      description="OK - List of Authenticate Client users",
 *      @OA\JsonContent(
 *          @OA\Property(property="meta", type="string",
 *              example={"total_users":372, "max_page_size":5}
 *          ),
 *          @OA\Property(property="links", type="string",
 *              example={
 *                  {"href":"/api/users?page=3&page_size=5", "rel":"self", "method":"GET"},
 *                  {"href":"/api/users?page=1&page_size=5", "rel":"first_page", "method":"GET"},
 *                  {"href":"/api/users?page=5&page_size=5", "rel":"last_page", "method":"GET"},
 *                  {"href":"/api/users?page=4&page_size=5", "rel":"next_page", "method":"GET"},
 *                  {"href":"/api/users?page=2&page_size=5", "rel":"previous_page", "method":"GET"}
 *              }
 *          ),
 *          @OA\Property(property="users", type="array",
 *              @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
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
 *      description="NOT FOUND - NO PRODUCT IN DATABASE",
 *      @OA\JsonContent(
 *        @OA\Property(property="code", type="string", example="404"),
 *        @OA\Property(property="message", type="string", example="The client have no user")
 *      )
 * )
 * @OA\Parameter(
 *      name="page",
 *      in="query",
 *      description="Page number",
 *      @OA\Schema(type="integer")
 * )
 * @OA\Parameter(
 *      name="page_size",
 *      in="query",
 *      description="Number of users per page",
 *      @OA\Schema(type="integer")
 * )

 * @OA\Tag(name="Users")
 */
#[Route('/api/users', name: 'app_users_list', methods: ['GET'], stateless: true)]
class GetUsersController extends AbstractController
{
    public function __invoke(
        Request $request,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        Paginator $paginator,
        Hateoas $hateoas,
        PaginateHateoas $paginateHateoas,
        CacheTools $cacheTools
    ): JsonResponse {

        $client = $this->getUser();
        $totalUsers = $userRepository->getTotalUsersByClient($client->getId());

        $paginator->setParams($totalUsers, $request->query->get('page'), $request->query->get('page_size'));

        // Cache item - return cache if item exist (users-list-page-page_size-client_id)
        $item = 'users-list-' . $paginator->getCurrentPage() . '-' . $paginator->getPageSize() . '-client_' . $client->getId();
        if ($cacheTools->setItem($item)) {
            return new JsonResponse($cacheTools->getItem(), Response::HTTP_OK, ["cache-control" => "max-age=60"], true);
        }

        $users = $userRepository->findUsersByClientPaginate($paginator->getPageSize(), $paginator->getCurrentPage(), $client->getId());

        foreach ($users as $user) {
            $user->setLinks([
                $hateoas->createLink('app_users_details', 'GET', 'self', ["id" => $user->getId()]),
                $hateoas->createLink('app_users_details', 'DELETE', 'delete_user', ["id" => $user->getId()])
            ]);
        }

        if ($users) {
            // return array["meta", "links"]
            $content = $paginateHateoas->createPaginateLinks($hateoas, $paginator);
            $content["links"][] = $hateoas->createLink('app_users_post', 'POST', 'new_user', []);
            $content["users"] = $users;

            $jsonUsersList = $serializer->serialize($content, 'json', ['groups' => 'getUsers']);
            $cacheTools->saveItem('usersList', $jsonUsersList);

            return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client have not user");
    }
}
