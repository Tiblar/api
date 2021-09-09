<?php
namespace App\Service\Billing;

use angelleye\PayPal\rest\billing\BillingAPI;
use Doctrine\ORM\EntityManagerInterface;

class PayPal {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var bool
     */
    private $sandbox;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    public function verify(string $request): bool
    {
        $raw_post_array = explode('&', $request);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        $gateway = "https://www.paypal.com/cgi-bin/webscr";

        if($this->sandbox){
            $gateway = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        }

        $ch = curl_init($gateway);
        if ($ch == false) {
            return false;
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: company-name'));
        $res = curl_exec($ch);

        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));

        if (strcmp($res, "VERIFIED") == 0 || strcasecmp($res, "VERIFIED") == 0) {
            return true;
        }

        return false;
    }
}