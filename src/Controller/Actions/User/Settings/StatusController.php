<?php

namespace App\Controller\Actions\User\Settings;

use App\Controller\ApiController;
use App\Entity\Media\File;
use App\Service\User\GetMe;
use App\Entity\User\User;
use App\Service\Content\Resource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends ApiController
{
    /**
     * @Route("/users/@me/status", name="change_status", methods={"PATCH"})
     */
    public function avatar(Request $request, GetMe $me)
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

        $status = $request->request->get('status');

        if(in_array($status, ['online', 'away', 'dnd', 'invisible'])){
            $user->getInfo()->setStatus($status);
            $em->flush();
        }

        return $this->respond($me->toArray($user));
    }
}