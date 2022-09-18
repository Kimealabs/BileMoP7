<?php

namespace App\Controller\Product;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
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
    public function __invoke(Request $request, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 5);
        $brand = (string) $request->query->get('brand', 'all');
        if ($page == 0) $page = 1;
        if ($limit == 0) $limit = 1;
        if ($limit > 5) $limit = 5;

        $totalProducts = $productRepository->getTotalProducts($brand);
        $totalPages = ceil($totalProducts / $limit);
        if ($totalPages < $page) $page = $totalPages;
        $products = $productRepository->findByPaginate($limit, $page, $brand);

        foreach ($products as $product) {
            $product->setLinks([
                [
                    "href" => $this->generateUrl('app_products_details', ["id" => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    "rel" => "self",
                    "method" => "GET"
                ]
            ]);
        }

        if ($products) {
            $content = [
                "meta" => [
                    "total products" => $totalProducts,
                    "first page" => 1,
                    "last page" => $totalPages,
                    "current page" => $page,
                    "limit" => $limit . " (max 5)"
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
        if ($brand == 'all') throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "No product in database");
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "No product with " . $brand . " for brand in database");
    }
}
