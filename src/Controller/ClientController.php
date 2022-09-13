<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ClientRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'app_clients', methods: ['GET'])]
    public function clientsList(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $clients = $clientRepository->findAll();
        if ($clients) {
            $jsonClientList = $serializer->serialize($clients, 'json', ['groups' => 'getClients']);
            return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
        }
        dd($clients);
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/clients/{id}', name: 'app_client', methods: ['GET'])]
    public function clientDetails(int $id, ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $client = $clientRepository->find($id);
        if ($client) {
            $jsonClient = $serializer->serialize($client, 'json', ['groups' => 'getClient']);
            return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
