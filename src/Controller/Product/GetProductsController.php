<?php

namespace App\Controller\Product;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/products', name: 'app_products_list', methods: ['GET'])]
#[IsGranted('ROLE_USER', message: 'You do not have the necessary rights for this resource')]
class GetProductsController extends AbstractController
{
    public function __invoke(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $products = $productRepository->findAll();
        foreach ($products as $product) {
            $product->setLinks([
                [
                    "href" => $this->generateUrl('app_products_details', ["id" => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    "rel" => "self",
                    "method" => "GET"
                ]
            ]);
        }
        $totalProducts = count($products);

        if ($products) {
            $content = [
                "meta" => [
                    "total" => $totalProducts
                ],
                "links" => [
                    [
                        "href" => $this->generateUrl('app_products_list', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "self",
                        "method" => "GET"
                    ]
                ],
                "products" => $products
            ];
            $jsonProductList = $serializer->serialize($content, 'json', ['groups' => 'getProducts']);
            return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NO_CONTENT, "No product in database");
    }
}
