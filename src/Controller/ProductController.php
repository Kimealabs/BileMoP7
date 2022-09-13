<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ClientRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'app_products', methods: ['GET'])]
    public function productsList(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $products = $productRepository->findAll();
        if ($products) {
            $jsonProductList = $serializer->serialize($products, 'json', ['groups' => 'getProducts']);
            return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/products/{id}', name: 'app_product', methods: ['GET'])]
    public function product(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');
        if ($jsonProduct) return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
