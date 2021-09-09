<?php

namespace App\Controller\Actions\User\Settings;

use App\Controller\ApiController;
use App\Entity\User\Addons\Follow;
use App\Entity\User\Addons\Privacy;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PrivacyController extends ApiController
{
    /**
     * @Route("/users/@me/settings/privacy/view", name="privacy_settings_view", methods={"PATCH"})
     */
    public function view(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $privacy = $em->getRepository(Privacy::class)->findOneBy([
            'userId' => $this->getUser()->getId()
        ]);

        if(!$privacy INSTANCEOF Privacy){
            return $this->respondWithErrors([
                'username' => 'This user does not have a privacy attachment. Contact support.'
            ], null, 404);
        }

        $view = $request->request->get('view');

        if($view === Privacy::$VIEW_EVERYONE){
            $privacy->setView(Privacy::$VIEW_EVERYONE);
        }

        if($view === Privacy::$VIEW_FORMERLY_CHUCKS){
            $privacy->setView(Privacy::$VIEW_FORMERLY_CHUCKS);
        }

        if($view === Privacy::$VIEW_FOLLOWERS){
            $privacy->setView(Privacy::$VIEW_FOLLOWERS);
        }

        $em->flush();

        $em->getConnection()->beginTransaction();

        try{
            if($view === Privacy::$VIEW_EVERYONE || $view === Privacy::$VIEW_FORMERLY_CHUCKS){
                $qb = $em->createQueryBuilder();

                $acceptIds = $qb->select('r.requesterId')
                    ->from('App:User\Addons\FollowRequest', 'r')
                    ->where('r.requestedId = :userId')
                    ->setParameter('userId', $this->getUser()->getId())
                    ->getQuery()
                    ->getArrayResult();
                $acceptIds = array_column($acceptIds, 'requesterId');

                if(empty($acceptIds)){
                    return $this->respond([
                        'privacy' => $privacy->toArray(),
                    ]);
                }

                $user = $em->getRepository(User::class)->findOneBy([
                   'id' => $privacy->getUserId()
                ]);

                if(!$user INSTANCEOF User){
                    return $this->respond([
                        'privacy' => $privacy->toArray(),
                    ]);
                }

                foreach($acceptIds as $i => $id){
                    $follow = new Follow();
                    $follow->setFollowedId($this->getUser()->getId());
                    $follow->setFollowerId($id);

                    $em->persist($follow);

                    if ($i % 200 === 0) {
                        $em->flush();
                    }
                }

                $user->getInfo()->setFollowerCount($user->getInfo()->getFollowerCount() + count($acceptIds));

               $qb->delete('App:User\Addons\FollowRequest', 'r')
                    ->where('r.requestedId = :userId')
                    ->setParameter('userId', $this->getUser()->getId())
                    ->getQuery()
                    ->getArrayResult();

                $em->flush();
            }

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
        }

        return $this->respond([
            'privacy' => $privacy->toArray(),
        ]);
    }

    /**
     * @Route("/users/@me/settings/privacy/content", name="privacy_settings_content", methods={"PATCH"})
     */
    public function content(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(Privacy::class)->findOneBy([
            'userId' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF Privacy){
            return $this->respondWithErrors([
                'username' => 'This user does not have a privacy attachment. Contact support.'
            ], null, 404);
        }

        $likes = $request->request->get('likes');
        $following = $request->request->get('following');
        $followerCount = $request->request->get('follower_count');

        if(is_bool($likes)){
            $user->setLikes(boolval($likes));
        }

        if(is_bool($following)){
            $user->setFollowing(boolval($following));
        }

        if(is_bool($followerCount)){
            $user->setFollowerCount(boolval($followerCount));
        }

        $em->flush();

        return $this->respond([
            'privacy' => $user->toArray(),
        ]);
    }

    /**
     * @Route("/users/@me/settings/privacy/action", name="privacy_settings_action", methods={"PATCH"})
     */
    public function action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(Privacy::class)->findOneBy([
            'userId' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF Privacy){
            return $this->respondWithErrors([
                'username' => 'This user does not have a privacy attachment. Contact support.'
            ], null, 404);
        }

        $asks = $request->request->get('asks');
        $reply = $request->request->get('reply');
        $message = $request->request->get('message');

        if(is_bool($asks)){
            $user->setAsks(boolval($asks));
        }

        if(is_bool($reply)){
            $user->setReply(boolval($reply));
        }

        if(is_bool($message)){
            $user->setMessage(boolval($message));
        }

        $em->flush();

        return $this->respond([
            'privacy' => $user->toArray(),
        ]);
    }

    /**
     * @Route("/users/@me/settings/privacy/formerly-chucks", name="privacy_settings_formerly_chucks", methods={"PATCH"})
     */
    public function formerlyChucks(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(Privacy::class)->findOneBy([
            'userId' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF Privacy){
            return $this->respondWithErrors([
                'username' => 'This user does not have a privacy attachment. Contact support.'
            ], null, 404);
        }

        $recommend = $request->request->get('recommend');

        if(is_bool($recommend)){
            $user->setRecommend(boolval($recommend));
        }

        $em->flush();

        return $this->respond([
            'privacy' => $user->toArray(),
        ]);
    }

    /**
     * @Route("/users/@me/settings/privacy/video-history", name="privacy_settings_video_history", methods={"PATCH"})
     */
    public function video_history(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(Privacy::class)->findOneBy([
            'userId' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF Privacy){
            return $this->respondWithErrors([
                'username' => 'This user does not have a privacy attachment. Contact support.'
            ], null, 404);
        }

        $history = $request->request->get('video_history');

        if(is_bool($history)){
            $user->setVideoHistory(boolval($history));
            $em->flush();
        }

        return $this->respond([
            'privacy' => $user->toArray(),
        ]);
    }
}