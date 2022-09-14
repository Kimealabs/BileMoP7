<?php

namespace App\Controller\Product;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/products', name: 'app_products_list', methods: ['GET'])]
class GetProductsController extends AbstractController
{
    public function __invoke(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $products = $productRepository->findAll();
        if ($products) {
            $jsonProductList = $serializer->serialize($products, 'json', ['groups' => 'getProducts']);
            return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "No product in database");
    }
}
