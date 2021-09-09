<?php

namespace App\Controller\Actions\User;

use App\Controller\ApiController;
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

class NotificationsController extends ApiController
{
    /**
     * @Route("/users/@me/notifications", name="get_me_notifications", methods={"GET"})
     */
    public function notifications(Request $request, Notifier $notifier)
    {
        return $this->respond([
            'notifications' => $notifier->fetch($this->getUser()->getId()),
        ]);
    }

    /**
     * @Route("/users/@me/notifications/count", name="get_me_notifications_count", methods={"GET"})
     */
    public function notificationsCount(Request $request, Notifier $notifier)
    {
        return $this->respond(
            $notifier->count($this->getUser()->getId())
        );
    }

    /**
     * @Route("/users/@me/notifications/causers/{notificationId}", name="get_me_notifications_causers", methods={"GET"})
     */
    public function notificationsCausers(Request $request, Notifier $notifier, $notificationId)
    {
        return $this->respond([
            'users' => $notifier->fetchCausers($this->getUser()->getId(), $notificationId),
        ]);
    }
}