<?php
namespace App\Controller\Matrix;

use App\Service\Generator\Securimage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WellKnownController extends AbstractController
{
    /**
     * @Route("/.well-known/matrix/server", name="matrix_server", methods={"GET"})
     */
    public function matrix(Request $request)
    {
        return new JsonResponse(["m.server" => $this->getParameter("matrix")['server']]);
    }
}
