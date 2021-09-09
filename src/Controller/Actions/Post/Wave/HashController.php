<?php

namespace App\Controller\Actions\Post\Wave;

use App\Controller\ApiController;
use App\Entity\Media\AudioWave;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HashController extends ApiController
{
    /**
     * @Route("/post/waveform/{hash}", name="wave_hash", methods={"GET"})
     */
    public function hash(Request $request, $hash)
    {
        $em = $this->getDoctrine()->getManager();

        $wave = $em->getRepository(AudioWave::class)->findOneBy([
            'hash' => $hash,
        ]);

        if(!$wave INSTANCEOF AudioWave){
            return $this->respondWithErrors([], null, 404);
        }

        return $this->respond([
            'wave' => $wave->getData()
        ]);
    }
}