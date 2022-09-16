<?php

namespace App\Controller\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users', name: 'app_users_list', methods: ['GET'])]
#[IsGranted('ROLE_USER', message: 'You do not have the necessary rights for this resource')]
class GetUsersController extends AbstractController
{
    public function __invoke(SerializerInterface $serializer): JsonResponse
    {
        $client = $this->getUser();
        $users = $client->getUsers();
        foreach ($users as $user) {
            $user->setLinks([
                "href" => $this->generateUrl('app_users_details', ["id" => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                "rel" => "self",
                "method" => "GET"
            ]);
        }

        if ($users) {
            $content = [
                "meta" => [
                    "total" => count($users)
                ],
                "links" => [
                    [
                        "href" => $this->generateUrl('app_users_list', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "self",
                        "method" => "GET"
                    ]
                ],
                "users" => $users
            ];

            $jsonUsersList = $serializer->serialize($content, 'json', ['groups' => 'getUsers']);
            return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NO_CONTENT, "This Client have not user");
    }
}
