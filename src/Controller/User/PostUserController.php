<?php

namespace App\Controller\User;

use OpenApi\Attributes as OA;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users', name: 'app_users_post', methods: ['POST'], stateless: true)]
#[OA\Get(
    path: '/api/users',
    description: "CREATE A NEW USER",
    responses: [
        new OA\Response(response: 202, description: 'CREATED - show header Location'),
        new OA\Response(response: 400, description: 'BAD REQUEST - A problem with body data'),
        new OA\Response(response: 401, description: 'UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token'),
    ]
)]
#[OA\Tag(name: 'Users')]
class PostUserController extends AbstractController
{
    public function __invoke(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManagerInterface,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        TagAwareCacheInterface $pool
    ): JsonResponse {

        $client = $this->getUser();
        $post = $request->getContent();
        $user = new User;
        $user->setClient($client);
        $serializer->deserialize($post, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, $errors[0]->getMessage());
        }

        $user->setClient($client);
        $entityManagerInterface->persist($user);
        $entityManagerInterface->flush();

        //DELETE usersList in pool (pagination and list are changed)
        $pool->invalidateTags(["usersList"]);

        $postJson = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
        $location = $urlGenerator->generate('app_users_details', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($postJson, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
