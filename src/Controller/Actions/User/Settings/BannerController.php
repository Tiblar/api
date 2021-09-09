<?php

namespace App\Controller\Actions\User\Settings;

use App\Controller\ApiController;
use App\Entity\Media\File;
use App\Service\User\GetMe;
use App\Entity\User\User;
use App\Service\Content\Resource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BannerController extends ApiController
{
    /**
     * @Route("/users/@me/settings/banner", name="change_banner", methods={"POST"})
     */
    public function avatar(Request $request, Resource $resourceFactory, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $uploadedFile = $request->files->get('file');

        if(is_null($uploadedFile)){
            $user->getInfo()->setBanner(null);
        }else{
            $resource = $resourceFactory->getBanner($uploadedFile);

            $fileRepository = $em->getRepository(File::class);
            $banner = $fileRepository->findOneBy([
                'hash' => $resource->getHash(),
            ]);

            if(!$banner INSTANCEOF File){
                $banner = new File();
                $banner->setFileSize($resource->getFileSize());
                $banner->setHash($resource->getHash());
                $banner->setHashName($resource->getHashName());
                $banner->setExtension("png");
                $banner->setURL($resource->upload());
                $banner->setHeight($resource->getHeight());
                $banner->setWidth($resource->getWidth());

                $em->persist($banner);
            }

            $user->getInfo()->setBanner($banner->getURL());
        }

        $em->flush();

        return $this->respond($me->toArray($user));
    }
}