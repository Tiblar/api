<?php
namespace App\Controller\Captcha;

use App\Service\Generator\Securimage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CaptchaController extends AbstractController
{
    /**
     * @Route("/captcha/{captchaId}", name="captcha")
     */
    public function captcha(Request $request, Securimage $securimage, $captchaId)
    {
        $securimage->show($captchaId);
    }
}
