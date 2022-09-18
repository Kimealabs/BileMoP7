<?php

namespace App\Controller\Product;

use App\Repository\ProductRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/api/products/{id}', name: 'app_products_details', methods: ['GET'], requirements: ['id' => '\d+'], stateless: true)]
class GetProductDetailsController extends AbstractController
{
    public function __invoke(
        int $id,
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        CacheItemPoolInterface $pool
    ): JsonResponse {

        // Cache item - return cache if item exist
        $item = 'products-details-' . $id;
        $productsDetailsItem = $pool->getItem($item);
        if ($productsDetailsItem->isHit()) {
            return new JsonResponse($productsDetailsItem->get(), Response::HTTP_OK, ["cache-control" => "cached item"], true);
        }

        $product = $productRepository->find($id);

        if (!$product) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Product don't exist");
        }

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

        // SET AND SAVE CACHE ITEM
        $jsonProduct = $serializer->serialize($content, 'json');
        $productsDetailsItem->set($jsonProduct);
        $pool->save($productsDetailsItem);
        sleep(3); // TO TEST CACHE WAY
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
