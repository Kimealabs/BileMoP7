<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users', name: 'app_users_list', methods: ['GET'], stateless: true)]
#[IsGranted('ROLE_USER', message: 'You do not have the necessary rights for this resource')]
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

        // Cache item - return cache if item exist
        $item = 'users-list-' . $page . '-' . $page_size;
        $usersListItem = $pool->getItem($item);
        if ($usersListItem->isHit()) {
            return new JsonResponse($usersListItem->get(), Response::HTTP_OK, ["cache-control" => "cached item"], true);
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
                    "total users" => $totalUsers,
                    "Max page_size" => 5
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
            $usersListItem->set($jsonUsersList);
            $pool->save($usersListItem);
            $usersListItem->tag("usersList");
            sleep(3); // TEST CACHE WAY

            return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Client have not user");
    }
}
