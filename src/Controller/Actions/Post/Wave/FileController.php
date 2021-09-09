<?php

namespace App\Controller\Actions\Post\Wave;

use App\Controller\ApiController;
use App\Entity\Media\AudioWave;
use App\Service\Post\WaveConverter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends ApiController
{
    /**
     * @Route("/post/waveform/file", name="wave_file", methods={"POST"})
     */
    public function wave(Request $request, WaveConverter $converter)
    {
        $file = $request->files->get('file');

        if(!$file INSTANCEOF UploadedFile){
            return $this->respondWithErrors([
                'file' => 'Parameter file required or it is invalid.'
            ], null, 400);
        }

       $waveData = $converter->file($file);

        if(is_null($waveData)){
            return $this->respondWithErrors([
                'file' => 'Parameter file required or it is invalid.'
            ], null, 400);
        }

        return $this->respond([
            'wave' => $waveData
        ]);
    }

    /**
     * @Route("/post/waveform/{hash}", name="wave_hash", methods={"POST"})
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