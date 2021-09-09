<?php

namespace App\Controller\Actions;

use App\Controller\ApiController;
use App\Entity\User\JwtRefreshToken;
use App\Service\Leaderboard;
use App\Structure\User\SanitizedUser;
use App\Entity\User\User;
use App\Entity\User\UserInfo;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

class InstanceController extends ApiController
{
    /**
     * @Route("/instance/stats", name="instance_stats")
     */
    public function stats(Request $request, Leaderboard $leaderboard)
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->createQuery('SELECT COUNT(u) FROM App:User\User u')
            ->getSingleScalarResult();

        $posts = $em->createQuery('SELECT COUNT(p) FROM App:Post\Post p')
            ->getSingleScalarResult();

        $storage = $em->createQueryBuilder()
            ->select('SUM(f.fileSize) as sum')
            ->from('App:Media\Attachment', 'a')
            ->leftJoin('a.file', 'f')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if(is_null($storage) || !isset($storage['sum'])){
            $storage = 0;
        }else{
            $storage = round($storage['sum'], 2);
        }

        $replies = $em->createQuery('SELECT COUNT(r) FROM App:Post\Reply r')
            ->getSingleScalarResult();

        return $this->respond([
            'stats' => [
                'users' => $users,
                'posts' => $posts,
                'storage' => $storage,
                'comments' => $replies,
            ],
            'ranks' => [
                'posts' => $leaderboard->posts(),
                'invites' => $leaderboard->invites(),
                'likes' => $leaderboard->favorites(),
                'followers' => $leaderboard->followers(),
            ]
        ]);
    }
}