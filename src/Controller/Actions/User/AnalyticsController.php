<?php

namespace App\Controller\Actions\User;

use App\Controller\ApiController;
use App\Entity\Analytics\ViewLog;
use App\Service\IntervalGenerator;
use App\Service\Post\Retrieve\Fetch\Multiple;
use App\Service\User\GetMe;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AnalyticsController extends ApiController
{
    /**
     * @Route("/users/@me/analytics/recent", name="get_analytics_recent", methods={"GET"})
     */
    public function recent(Request $request, Multiple $multiple)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if (!$user instanceof User) {
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $intervals = IntervalGenerator::getIntervalArray(new \DateTime("-2 days"), new \DateTime("now"), 60);

        $last48HoursPostsData = $em->createQueryBuilder()
            ->select('COUNT(l) as count, FROM_UNIXTIME((FLOOR(UNIX_TIMESTAMP(l.timestamp)/(60*60))*(60*60))) AS timestamp')
            ->from('App:Analytics\ViewLog', 'l')
            ->where('l.userId = :userId')
            ->andWhere('l.type = :type')
            ->andWhere('l.timestamp > :timestamp')
            ->setParameter('userId', $user->getId())
            ->setParameter('type', ViewLog::$VIEW_TYPE_POST)
            ->setParameter('timestamp', new \DateTime("-2 days"))
            ->groupBy('timestamp')
            ->getQuery()
            ->getArrayResult();

        foreach($last48HoursPostsData as &$datum){
            $timestamp = new \DateTime($datum['timestamp']);
            $datum['timestamp'] = $timestamp->format("c");
        }

        $last48HoursPosts = [];
        foreach($intervals as $period){
            $count = 0;

            $key = array_search($period, array_column($last48HoursPostsData, "timestamp"));

            if($key !== false){
                if(isset($last48HoursPostsData[$key]) && isset($last48HoursPostsData[$key]['count'])){
                    $count = $last48HoursPostsData[$key]['count'];
                }
            }

            $last48HoursPosts[] = [
                'count' => $count,
                'timestamp' => $period,
            ];
        }

        $last48HoursProfileData = $em->createQueryBuilder()
            ->select('COUNT(l) as count, FROM_UNIXTIME((FLOOR(UNIX_TIMESTAMP(l.timestamp)/(60*60))*(60*60))) AS timestamp')
            ->from('App:Analytics\ViewLog', 'l')
            ->where('l.userId = :userId')
            ->andWhere('l.type = :type')
            ->andWhere('l.timestamp > :timestamp')
            ->setParameter('userId', $user->getId())
            ->setParameter('type', ViewLog::$VIEW_TYPE_USER)
            ->setParameter('timestamp', new \DateTime("-2 days"))
            ->groupBy('timestamp')
            ->getQuery()
            ->getArrayResult();

        foreach($last48HoursProfileData as &$datum){
            $timestamp = new \DateTime($datum['timestamp']);
            $datum['timestamp'] = $timestamp->format("c");
        }

        $last48HoursProfile = [];
        foreach($intervals as $period){
            $count = 0;

            $key = array_search($period, array_column($last48HoursProfileData, "timestamp"));

            if($key !== false){
                if(isset($last48HoursProfileData[$key]) && isset($last48HoursProfileData[$key]['count'])){
                    $count = $last48HoursProfileData[$key]['count'];
                }
            }

            $last48HoursProfile[] = [
                'count' => $count,
                'timestamp' => $period,
            ];
        }

        $topPosts = $em->createQueryBuilder()
            ->select('COUNT(l) as count, l.resourceId as postId')
            ->from('App:Analytics\ViewLog', 'l')
            ->where('l.userId = :userId')
            ->andWhere('l.type = :type')
            ->andWhere('l.timestamp > :timestamp')
            ->setParameter('userId', $user->getId())
            ->setParameter('type', ViewLog::$VIEW_TYPE_POST)
            ->setParameter('timestamp', new \DateTime("-2 days"))
            ->setMaxResults(3)
            ->orderBy('count', 'desc')
            ->groupBy('l.resourceId')
            ->getQuery()
            ->getArrayResult();
        $topPosts = array_column($topPosts, "postId");

        $sources = $em->createQueryBuilder()
            ->select('COUNT(l) as count, l.source as source')
            ->from('App:Analytics\ViewLog', 'l')
            ->where('l.userId = :userId')
            ->andWhere('l.type = :type')
            ->andWhere('l.timestamp > :timestamp')
            ->setParameter('userId', $user->getId())
            ->setParameter('type', ViewLog::$VIEW_TYPE_POST)
            ->setParameter('timestamp', new \DateTime("-2 days"))
            ->groupBy('l.source')
            ->getQuery()
            ->getArrayResult();

        return $this->respond([
            'recent_views' => [
                'posts' => $last48HoursPosts,
                'profile' => $last48HoursProfile,
            ],
            'top_posts' => $multiple->multiple($topPosts, 3),
            'sources' => $sources,
        ]);
    }

    /**
     * @Route("/users/@me/analytics/historical", name="get_analytics_historical", methods={"GET"})
     */
    public function historical(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if (!$user instanceof User) {
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $period = intval($request->query->get('period'));

        $timestamp = new \DateTime();
        switch($period){
            case 2:
                $timestamp->modify("-180 days");
                break;
            case 3:
                $timestamp->modify("-365 days");
                break;
            case 4:
                $timestamp = (clone $user->getInfo()->getJoinDate());
                break;
            default:
                $timestamp->modify("-30 days");
                break;
        }

        $timestamp->setTime(0, 0, 0);
        $intervals = IntervalGenerator::getIntervalArray((clone $timestamp), new \DateTime("-3 day"), 60*24);

        $postViewsData = $em->createQueryBuilder()
            ->select('SUM(p.views) as count, p.timestamp AS timestamp')
            ->from('App:Analytics\PostAnalytics', 'p')
            ->where('p.userId = :userId')
            ->andWhere('p.timestamp > :timestamp')
            ->setParameter('userId', $user->getId())
            ->setParameter('timestamp', $timestamp)
            ->groupBy('timestamp')
            ->getQuery()
            ->getArrayResult();

        foreach($postViewsData as &$datum){
            $datum['timestamp']->setTime(0, 0, 0);
            $datum['timestamp'] = $datum['timestamp']->format("c");
        }

        $postViews = [];
        foreach($intervals as $period){
            $count = 0;

            $key = array_search($period, array_column($postViewsData, "timestamp"));

            if($key !== false){
                if(isset($postViewsData[$key]) && isset($postViewsData[$key]['count'])){
                    $count = $postViewsData[$key]['count'];
                }
            }

            $postViews[] = [
                'count' => $count,
                'timestamp' => $period,
            ];
        }

        $userViewsData = $em->createQueryBuilder()
            ->select('SUM(p.views) as count, p.timestamp AS timestamp')
            ->from('App:Analytics\UserAnalytics', 'p')
            ->where('p.userId = :userId')
            ->andWhere('p.timestamp > :timestamp')
            ->setParameter('userId', $user->getId())
            ->setParameter('timestamp', $timestamp)
            ->groupBy('timestamp')
            ->getQuery()
            ->getArrayResult();

        foreach($userViewsData as &$datum){
            $datum['timestamp']->setTime(0, 0, 0);
            $datum['timestamp'] = $datum['timestamp']->format("c");
        }

        $userViews = [];
        foreach($intervals as $period){
            $count = 0;

            $key = array_search($period, array_column($userViewsData, "timestamp"));

            if($key !== false){
                if(isset($userViewsData[$key]) && isset($userViewsData[$key]['count'])){
                    $count = $userViewsData[$key]['count'];
                }
            }

            $userViews[] = [
                'count' => $count,
                'timestamp' => $period,
            ];
        }

        $likesData = $em->createQueryBuilder()
            ->select('COUNT(f) as count, FROM_UNIXTIME((FLOOR(UNIX_TIMESTAMP(f.timestamp)/(60*60*24))*(60*60*24))) AS timestamp')
            ->from('App:Post\Favorite', 'f')
            ->where('f.favorited = :userId')
            ->andWhere('f.timestamp > :timestamp')
            ->setParameter('userId', $user->getId())
            ->setParameter('timestamp', $timestamp)
            ->groupBy('timestamp')
            ->getQuery()
            ->getArrayResult();

        foreach($likesData as &$datum){
            $likeTimestamp = new \DateTime($datum['timestamp']);
            $likeTimestamp->setTime(0, 0, 0);
            $datum['timestamp'] = $likeTimestamp->format("c");
        }

        $likes = [];
        foreach($intervals as $period){
            $count = 0;

            $key = array_search($period, array_column($likesData, "timestamp"));

            if($key !== false){
                if(isset($likesData[$key]) && isset($likesData[$key]['count'])){
                    $count = $likesData[$key]['count'];
                }
            }

            $likes[] = [
                'count' => $count,
                'timestamp' => $period,
            ];
        }

        $followerData = $em->createQueryBuilder()
            ->select('COUNT(f) as count, FROM_UNIXTIME((FLOOR(UNIX_TIMESTAMP(f.timestamp)/(60*60*24))*(60*60*24))) AS timestamp')
            ->from('App:User\Addons\Follow', 'f')
            ->where('f.followedId = :userId')
            ->andWhere('f.timestamp > :timestamp')
            ->setParameter('userId', $user->getId())
            ->setParameter('timestamp', $timestamp)
            ->groupBy('timestamp')
            ->getQuery()
            ->getArrayResult();

        foreach($followerData as &$datum){
            $followTimestamp = new \DateTime($datum['timestamp']);
            $followTimestamp->setTime(0, 0, 0);
            $datum['timestamp'] = $followTimestamp->format("c");
        }

        $followers = [];
        foreach($intervals as $period){
            $count = 0;

            $key = array_search($period, array_column($followerData, "timestamp"));

            if($key !== false){
                if(isset($followerData[$key]) && isset($followerData[$key]['count'])){
                    $count = $followerData[$key]['count'];
                }
            }

            $followers[] = [
                'count' => $count,
                'timestamp' => $period,
            ];
        }

        return $this->respond([
            'posts' => $postViews,
            'user' => $userViews,
            'favorites' => $likes,
            'followers' => $followers,
        ]);
    }
}
