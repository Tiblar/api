<?php

namespace App\Controller\Actions\User\Connections;

use App\Controller\ApiController;
use App\Entity\User\Addons\Connection;
use App\Entity\User\Addons\Follow;
use App\Entity\User\Addons\FollowRequest;
use App\Entity\User\Addons\Privacy;
use App\Service\User\Block;
use App\Service\User\GetMe;
use App\Entity\User\User;
use App\Service\User\Notifier;
use App\Structure\User\SanitizedFollowRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends ApiController
{
    /**
     * @Route("/users/@me/connections/discord/status", name="connections_status_discord", methods={"GET"})
     */
    public function discord(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $connection = $em->getRepository(Connection::class)->findOneBy([
            'userId' => $this->getUser()->getId(),
            'service' => Connection::$SERVICE_DISCORD
        ]);

        if($connection INSTANCEOF Connection){
            return $this->respond([
                'connection' => $connection->toArray(),
            ]);
        }

        return $this->respondWithErrors([], null, 404);
    }
}