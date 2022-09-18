<?php

namespace App\Controller\Product;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/products/{id}', name: 'app_products_details', methods: ['GET'], requirements: ['id' => '\d+'])]
class GetProductDetailsController extends AbstractController
{
    public function __invoke(int $id, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Product don't exist");
        }

        $product->removeLinks();
        $content = [
            "links" => [
                [
                    "href" => $this->generateUrl('app_products_details', ["id" => $id], UrlGeneratorInterface::ABSOLUTE_URL),
                    "rel" => "self",
                    "type" => "GET"
                ],
            ],
            "product" => $product
        ];

        $jsonProduct = $serializer->serialize($content, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
