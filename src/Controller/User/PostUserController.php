<?php

namespace App\Controller\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users', name: 'app_users_post', methods: ['POST'])]
#[IsGranted('ROLE_USER', message: 'You do not have the necessary rights for this resource')]
class PostUserController extends AbstractController
{
    public function __invoke(
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManagerInterface,
        ValidatorInterface $validator,
        SerializerInterface $serializer
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

        $postJson = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
        $location = $urlGenerator->generate('app_user_details', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($postJson, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
