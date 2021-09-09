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

class DisconnectController extends ApiController
{
    /**
     * @Route("/users/@me/connections/discord", name="disconnect_discord", methods={"DELETE"})
     */
    public function discord(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $em->createQueryBuilder()
            ->delete('App:User\Addons\Connection', 'c')
            ->where('c.service = :service')
            ->andWhere('c.userId = :userId')
            ->setParameter('service', Connection::$SERVICE_DISCORD)
            ->setParameter('userId', $this->getUser()->getId())
            ->getQuery()->getResult();


        return $this->respond([]);
    }
}