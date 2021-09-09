<?php

namespace App\Controller\Actions\Market\Product;

use App\Controller\ApiController;
use App\Entity\Billing\Product;
use App\Service\Billing\GetBilling;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetController extends ApiController
{
    /**
     * @Route("/market/product/{productId}", name="market_get_product", methods={"GET"})
     */
    public function product(Request $request, GetBilling $billing, $productId)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository(Product::class)->findOneBy([
            'id' => $productId,
        ]);

        if(!$product instanceof Product){
            return $this->respondWithErrors([
                'id' => 'Product not found.'
            ], null, 404);
        }

        $userId = $this->getUser() ? $this->getUser()->getId() : null;
        $productUserId = $product->getUser() ? $product->getUser()->getId() : null;
        if($productUserId !== $userId && !$product->isPublished()){
            return $this->respondWithErrors([
                'id' => 'Order not found.'
            ], null, 404);
        }

        return $this->respond([
            'product' => $billing->productToArray($product)
        ]);
    }
}