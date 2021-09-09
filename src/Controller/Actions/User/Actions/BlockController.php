<?php

namespace App\Controller\Actions\User\Actions;

use App\Controller\ApiController;
use App\Entity\User\Addons\Block;
use App\Entity\User\Addons\Follow;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlockController extends ApiController
{
    /**
     * @Route("/users/block/{blockId}", name="block_user", methods={"POST"})
     */
    public function block(Request $request, $blockId)
    {
        $em = $this->getDoctrine()->getManager();

        $blockUser = $em->getRepository(User::class)->findOneBy([
            'id' => $blockId
        ]);

        if(!$blockUser INSTANCEOF User || $blockId === $this->getUser()->getId()){
            return $this->respondWithErrors([
                'id' => 'This user does not exist.'
            ], null, 404);
        }

        $block = $em->getRepository(Block::class)->findOneBy([
            'blockedId' => $blockId,
            'blockerId' => $this->getUser()->getId()
        ]);

        if($block INSTANCEOF Block){
            return $this->respond([
                'block' => $block->toArray(),
            ]);
        }


        $block = new Block();
        $block->setBlockerId($this->getUser()->getId());
        $block->setBlockedId($blockId);

        $em->persist($block);

        $count = $em->createQueryBuilder()
            ->delete('App:User\Addons\Follow', 'f')
            ->where('f.followerId = :userId AND f.followedId = :blockId')
            ->orWhere('f.followerId = :blockId AND f.followedId = :userId')
            ->setParameter('blockId', $blockId)
            ->setParameter('userId', $this->getUser()->getId())
            ->getQuery()->getResult();

        if($count){
            $user = $em->getRepository(User::class)->findOneBy([
               'id' => $this->getUser()->getId()
            ]);

            if($user INSTANCEOF User){
                $user->getInfo()->setFollowerCount($user->getInfo()->getFollowerCount() - 1);
            }
        }

        $em->flush();

        return $this->respond([
            'block' => $block->toArray(),
        ]);
    }

    /**
     * @Route("/users/block/{blockId}", name="unblock_user", methods={"DELETE"})
     */
    public function unblock(Request $request, $blockId)
    {
        $em = $this->getDoctrine()->getManager();

        $block = $em->getRepository(Block::class)->findOneBy([
            'blockedId' => $blockId,
            'blockerId' => $this->getUser()->getId()
        ]);

        if($block INSTANCEOF Block){
            $em->remove($block);
            $em->flush();
        }else{
            return $this->respondWithErrors([], null, 404);
        }

        return $this->respond([]);
    }
}