<?php

namespace App\Controller\User;

use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;
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
/**
 * @OA\Post(
 *      summary="Create user",
 *      operationId="postUser",
 *      @OA\Response(
 *          response=400,
 *          description="Invalid fields"
 *      ),
 *      @OA\RequestBody(
 *          description="Create a new user",
 *          required=true,
 *          @OA\JsonContent(
 *              example={
 *               "firstname" : "John",
 *               "secondname": "Doe",
 *               "email": "johndoe@nobody.org",
 *               "address": "123 Strange street, CA"
 *              },
 *              @OA\Schema(
 *                  type="object",
 *                  @OA\Property(property="firstname", required=true, description="Firstname", type="string"),
 *                  @OA\Property(property="secondname", required=true, description="Secondname", type="string"),
 *                  @OA\Property(property="email", required=true, description="Email", type="string"),
 *                  @OA\Property(property="address", required=true, description="Address", type="string")
 *              )
 *          )
 *      )
 * )
 * @OA\Tag(name="Users")
 */
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
