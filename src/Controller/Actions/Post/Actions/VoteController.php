<?php

namespace App\Controller\Actions\Post\Actions;

use App\Controller\ApiController;
use App\Entity\Media\Poll;
use App\Entity\Media\PollVote;
use App\Entity\Post\Post;
use App\Service\User\Block;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VoteController extends ApiController
{
    /**
     * @Route("/post/vote/{postId}", name="vote_post", methods={"POST"})
     */
    public function favorite(Request $request, Block $blockService, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId
        ]);

        $block = $blockService->get($post->getAuthor()->getId());

        if(in_array($this->getUser()->getId(), $block->getBlocking())){
            return $this->respondWithErrors([], "You have been blocked by this user.", 403);
        }

        $poll = $em->getRepository(Poll::class)->findOneBy([
            'postId' => $postId
        ]);

        if(!$post INSTANCEOF Post || !$poll INSTANCEOF Poll || $poll->isExpired()){
            return $this->respondWithErrors([
                'id' => 'This post does not exist.'
            ], null, 404);
        }

        $vote = $em->getRepository(PollVote::class)->findOneBy([
            'postId' => $post->getId(),
            'userId' => $this->getUser()->getId()
        ]);

        if($vote INSTANCEOF PollVote){
            return $this->respond([
                'vote' => $vote->toArray(),
            ]);
        }

        $vote = new PollVote();
        $vote->setPostId($postId);
        $vote->setUserId($this->getUser()->getId());

        $option = $request->request->get('option');

        if(is_null($option)){
            return $this->respondWithErrors([
                'option' => 'This option does not exist.'
            ], null, 400);
        }

        $option = intval($option);

        if($option === 1){
            $vote->setChoice(1);
            $poll->setO1VotesCount($poll->getO1VotesCount() + 1);
        }elseif($option === 2){
            $vote->setChoice(2);
            $poll->setO2VotesCount($poll->getO2VotesCount() + 1);
        }elseif($option === 3 && !is_null($poll->getOptionThree())){
            $vote->setChoice(3);
            $poll->setO3VotesCount($poll->getO3VotesCount() + 1);
        }elseif($option === 4 && !is_null($poll->getOptionFour())){
            $vote->setChoice(4);
            $poll->setO4VotesCount($poll->getO4VotesCount() + 1);
        }else{
            return $this->respondWithErrors([
                'option' => 'This option does not exist.'
            ], null, 400);
        }

        $em->persist($vote);

        $poll->setVotesCount($poll->getVotesCount() + 1);

        $em->flush();

        return $this->respond([
            'vote' => $vote->toArray(),
        ]);
    }
}