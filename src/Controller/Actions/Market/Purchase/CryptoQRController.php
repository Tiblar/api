<?php

namespace App\Controller\Actions\Market\Purchase;

use App\Entity\Billing\CryptoPaymentMethod;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\PaymentMethod;
use Endroid\QrCode\QrCode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CryptoQRController extends AbstractController
{
    /**
     * @Route("/market/purchase/qr/{invoiceId}", name="market_crypto_qr_product", methods={"GET"})
     */
    public function product(Request $request, $invoiceId)
    {
        $em = $this->getDoctrine()->getManager();

        $invoice = $em->getRepository(Invoice::class)->findOneBy([
            'id' => $invoiceId
        ]);

        if(
            !$invoice instanceof Invoice ||
            ($invoice->getSellerId() !== $this->getUser()->getId() &&
                $invoice->getBuyerId() !== $this->getUser()->getId())
        ){
            throw new NotFoundHttpException("Invoice not found.");
        }

        if(!($invoice->getPaymentMethod() instanceof PaymentMethod)){
            throw new NotFoundHttpException("Invoice not found.");
        }


        $crypto = $invoice->getPaymentMethod()->getCryptoPaymentMethod();
        if(!($crypto instanceof CryptoPaymentMethod)){
            throw new NotFoundHttpException("Invoice not found.");
        }

        $type = strtolower($crypto->getType());

        $qr = new QrCode($type . ":" . $crypto->getAddress() . "?amount=" . $crypto->getAmount());
        $qr->setSize(230);

        return new Response($qr->writeString(), 200, [
            'Content-Type' => $qr->getContentType()
        ]);
    }
}
