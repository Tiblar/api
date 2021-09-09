<?php

namespace App\Controller\Actions\User\Connections;

use App\Controller\ApiController;
use App\Entity\User\Addons\Connection;
use App\Entity\User\Addons\Follow;
use App\Entity\User\Addons\FollowRequest;
use App\Entity\User\Addons\Privacy;
use App\Service\Connection\Discord\DiscordConnect;
use App\Service\User\Block;
use App\Service\User\GetMe;
use App\Entity\User\User;
use App\Service\User\Notifier;
use App\Structure\User\SanitizedFollowRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConnectController extends ApiController
{
    /**
     * @Route("/users/@me/connections/discord", name="connect_discord", methods={"POST"})
     */
    public function discord(Request $request, DiscordConnect $connect)
    {
        $em = $this->getDoctrine()->getManager();

        $code = $request->request->get('code');

        if(is_null($code)){
            return $this->respondWithErrors([
                'code' => "The parameter code is a required variable."
            ], null, 400);
        }

        $connection = $em->getRepository(Connection::class)->findOneBy([
            'userId' => $this->getUser()->getId(),
            'service' => Connection::$SERVICE_DISCORD
        ]);

        if($connection INSTANCEOF Connection){
            return $this->respond([
                'connection' => $connection->toArray(),
            ]);
        }

        $connect->validate($code);

        $connection = $em->getRepository(Connection::class)->findOneBy([
            'userId' => $this->getUser()->getId(),
            'service' => Connection::$SERVICE_DISCORD
        ]);

        if($connection INSTANCEOF Connection){
            return $this->respond([
                'connection' => $connection->toArray()
            ]);
        }

        return $this->respondWithErrors([
            'code' => "You entered an invalid code."
        ], null, 400);
    }
}