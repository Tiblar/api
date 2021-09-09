<?php
namespace App\Controller\Actions;

use App\Controller\ApiController;
use App\Service\Generator\Securimage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CaptchaController extends ApiController
{
    /**
     * @Route("/captcha/generate", name="captcha_generate", methods={"GET"})
     */
    public function captcha(Request $request, Securimage $securimage)
    {
        $arr = $securimage->createCode();

        return $this->respond($arr);
    }
}
