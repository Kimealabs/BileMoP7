<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ClientRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    // #[Route('/api/products', name: 'app_products', methods: ['GET'])]
    // public function productsList(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    // {
    //     $products = $productRepository->findAll();
    //     if ($products) {
    //         $jsonProductList = $serializer->serialize($products, 'json', ['groups' => 'getProducts']);
    //         return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    //     }
    //     throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "No product in database");
    // }

    // #[Route('/api/products/{id}', name: 'app_product', methods: ['GET'])]
    // public function product(int $id, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    // {
    //     $product = $productRepository->find($id);

    //     if (!$product) {
    //         throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Product don't exist");
    //     }

    //     $jsonProduct = $serializer->serialize($product, 'json');
    //     return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    // }
}
