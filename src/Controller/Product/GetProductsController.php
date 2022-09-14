<?php

namespace App\Controller\Product;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/api/products', name: 'app_products_list', methods: ['GET'])]
#[IsGranted('ROLE_USER', message: 'You do not have the necessary rights for this resource')]
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
