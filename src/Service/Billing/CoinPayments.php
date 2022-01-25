<?php
namespace App\Service\Billing;

use App\Entity\Billing\Invoice;
use App\Entity\Billing\Order;
use App\Entity\Billing\PaymentMethod;
use Psr\Log\LoggerInterface;
use CoinpaymentsAPI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class CoinPayments
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CoinpaymentsAPI
     */
    private $coinPayments;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $ipnURL;

    /**
     * @var string
     */
    private $ipnSecret;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $em, Security $security,
        string $privateKey, string $publicKey,
        string $merchantId, string $ipnURL,
        string $ipnSecret,
        LoggerInterface $logger
    )
    {
        $this->em = $em;
        $this->security = $security;
        $this->coinPayments = new CoinpaymentsAPI($privateKey, $publicKey, 'json');
        $this->merchantId = $merchantId;
        $this->ipnURL = $ipnURL;
        $this->ipnSecret = $ipnSecret;
        $this->logger = $logger;
    }

    /**
     * @param Invoice $invoice
     * @param Order $order
     * @param string $paymentMethod
     * @return array|object|null
     * @throws \Exception
     */
    public function createTransaction(Invoice $invoice, Order $order, string $paymentMethod)
    {
        $crypto = null;
        if($paymentMethod === PaymentMethod::$TYPE_BITCOIN){
            $crypto = "BTC";
        }

        if($paymentMethod === PaymentMethod::$TYPE_MONERO){
            $crypto = "XMR";
        }

        if($paymentMethod === PaymentMethod::$TYPE_LITECOIN){
            $crypto = "LTC";
        }

        if($paymentMethod === PaymentMethod::$TYPE_BITCOIN_CASH){
            $crypto = "BCH";
        }

        if($paymentMethod === PaymentMethod::$TYPE_TETHER){
            $crypto = "USDT.TRC20";
        }

        if($paymentMethod === PaymentMethod::$TYPE_USD_COIN){
            $crypto = "USDC.TRC20";
        }

        if($paymentMethod === PaymentMethod::$TYPE_DOGE){
            $crypto = "DOGE";
        }

        if(is_null($crypto)){
            throw new \Exception("Invalid payment method.");
        }

        $product = $order->getProduct();
        $price = $order->getPrice();

        $transaction = null;
        try{
            $transaction = $this->coinPayments->CreateComplexTransaction(
                $price,
                $order->getCurrency(),
                $crypto,
                "noreply@formerlychucks.net",
                "",
                "",
                $product->getTitle(),
                $product->getId(),
                $invoice->getId(),
                "",
                $this->ipnURL
            );
        }catch (\Exception $e){
            $this->logger->error($e->getMessage());
            return null;
        }

        if(isset($transaction['error']) && $transaction['error'] !== "ok") {
            $this->logger->error("CoinPayments error: {$transaction['error']}");
            return null;
        }

        if(isset($transaction['result'])){
            $transaction = $transaction['result'];
        }else{
            $this->logger->error("Transaction is empty.");
            return null;
        }

        if(
            !isset($transaction['amount']) || !isset($transaction['address']) ||
            !isset($transaction['txn_id']) || !isset($transaction['timeout'])
        ){
            $this->logger->error("Transaction does not have crypto info.");
            return null;
        }
        $this->logger->error($transaction['amount']);

        return $transaction;
    }

    public function validateIPN(Request $request)
    {
        if(!$request->server->has('HTTP_HMAC') || empty($request->server->get('HTTP_HMAC'))){
            throw new \Exception("HTTP_HMAC not found.");
        }

        $merchant = $request->request->has('merchant') ? $request->request->get('merchant') : '';
        if(empty($merchant)){
            throw new \Exception("Merchant not found.");
        }

        if($merchant !== $this->merchantId) {
            throw new \Exception("Merchant ID does not match.");
        }

        $hmac = hash_hmac("sha512", $request->getContent(false), $this->ipnSecret);
        if($hmac != $request->server->get('HTTP_HMAC')){
            throw new \Exception("HMAC signature does not match.");
        }

        return true;
    }
}