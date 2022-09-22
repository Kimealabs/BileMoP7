<?php

namespace App\Controller\User;

use App\Entity\User;
use OpenApi\Annotations as OA;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
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
        TagAwareCacheInterface $pool
    ): JsonResponse {

        $page = (int) $request->query->get('page', 1);
        $page_size = (int) $request->query->get('page_size', 5);
        if ($page == 0) $page = 1;
        if ($page_size == 0) $page_size = 1;
        if ($page_size > 5) $page_size = 5;

        $client = $this->getUser();

        $totalUsers = $userRepository->getTotalUsersByClient($client->getId());
        $totalPages = ceil($totalUsers / $page_size);
        if ($totalPages < $page) $page = $totalPages;
        $nextPage = ($page < $totalPages) ? $page + 1 : null;
        $previousPage = ($page > 1) ? $page - 1 : null;
        $currentPage = $page;

        // Cache item - return cache if item exist (users-list-page-page_size-client_id)
        $item = 'users-list-' . $page . '-' . $page_size . '-client_' . $client->getId();
        if ($pool->hasItem($item)) {
            return new JsonResponse($pool->getItem($item)->get(), Response::HTTP_OK, ["cache-control" => "max-age=60"], true);
        }

        $users = $userRepository->findUsersByClientPaginate($page_size, $page, $client->getId());

        foreach ($users as $user) {
            $user->setLinks([
                [
                    "href" => $this->generateUrl('app_users_details', ["id" => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    "rel" => "self",
                    "method" => "GET"
                ],
                [
                    "href" => $this->generateUrl('app_users_delete', ["id" => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    "rel" => "Delete user",
                    "method" => "DELETE"
                ]
            ]);
        }

        if ($users) {
            $content = [
                "meta" => [
                    "total_users" => $totalUsers,
                    "max_page_size" => 5
                ],
                "links" => [
                    [
                        "href" => $this->generateUrl('app_users_list', ["page" => $currentPage, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "self",
                        "method" => "GET"
                    ],
                    [
                        "href" => $this->generateUrl('app_users_list', ["page" => 1, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "first page",
                        "method" => "GET"
                    ],
                    [
                        "href" => $this->generateUrl('app_users_list', ["page" => $totalPages, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "last page",
                        "method" => "GET"
                    ]
                ]
            ];
            if ($nextPage !== null) {
                $content["links"][] =
                    [
                        "href" => $this->generateUrl('app_users_post', ["page" => $nextPage, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "next page",
                        "method" => "GET"
                    ];
            }
            if ($previousPage !== null) {
                $content["links"][] =
                    [
                        "href" => $this->generateUrl('app_users_post', ["page" => $previousPage, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "previous page",
                        "method" => "GET"
                    ];
            }
            $content["links"][] = [
                "href" => $this->generateUrl('app_users_post', [], UrlGeneratorInterface::ABSOLUTE_URL),
                "rel" => "New user",
                "method" => "POST"
            ];

            $content["users"] = $users;


            $jsonUsersList = $serializer->serialize($content, 'json', ['groups' => 'getUsers']);
            $usersListItem = $pool->getItem($item);
            $usersListItem->set($jsonUsersList);
            $usersListItem->tag("usersList");
            $usersListItem->expiresAfter(60);
            $pool->save($usersListItem);
            sleep(3); // TEST CACHE WAY

            return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client have not user");
    }
}
