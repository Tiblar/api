<?php

namespace App\Controller\Actions\Video;

use App\Controller\ApiController;
use App\Entity\Video\VideoHistory;
use App\Service\Post\Retrieve\Fetch\Multiple;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends ApiController
{
    /**
     * @Route("/video/history", name="video_history", methods={"GET"})
     */
    public function getHistory(Request $request, Multiple $fetchPosts)
    {
        $limit = intval($request->query->get('limit'));
        $offset = intval($request->query->get('offset'));

        if($limit > 100){
            $limit = 100;
        }

        if($limit < 10){
            $limit = 10;
        }

        $em = $this->getDoctrine()->getManager();
        $historyIds = $em->createQueryBuilder()
            ->select('h.id, h.postId')
            ->from('App:Video\VideoHistory', 'h')
            ->where('h.userId = :userId')
            ->setParameter('userId', $this->getUser()->getId())
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('h.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
        $videoIds = array_column($historyIds, "postId");

        $posts = $fetchPosts->multiple($videoIds);

        $history = [];
        foreach($historyIds as $hItem){
            foreach($posts as $pItem){
                if($pItem['id'] === $hItem['postId']){
                    $history[] = [
                        'id' => $hItem['id'],
                        'post' => $pItem,
                    ];
                }
            }
        }

        return $this->respond(['history' => $history]);
    }

    /**
     * @Route("/video/history/{id}", name="video_history_delete_id", methods={"DELETE"})
     */
    public function deleteHistoryId(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $history = $em->getRepository(VideoHistory::class)->findOneBy([
           'id' => $id,
           'userId' => $this->getUser()->getId(),
        ]);

        if(!($history instanceof VideoHistory)){
            return $this->respondWithErrors([
                'id' => "Video history not found with that post ID.",
            ], null, 404);
        }

        $em->remove($history);
        $em->flush();

        return $this->respond([]);
    }


    /**
     * @Route("/video/history", name="video_history_delete", methods={"DELETE"})
     */
    public function deleteHistory(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $em->createQueryBuilder()
            ->delete('App:Video\VideoHistory', 'h')
            ->where('h.userId = :userId')
            ->setParameter('userId', $this->getUser()->getId())
            ->getQuery()
            ->getArrayResult();

        return $this->respond([]);
    }
}