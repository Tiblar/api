<?php

namespace App\Controller\Actions\Post\Actions;

use App\Controller\ApiController;
use App\Entity\Post\Post;
use App\Entity\User\Addons\Pin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PinController extends ApiController
{
    /**
     * @Route("/post/pin/{postId}", name="pin_post", methods={"POST"})
     */
    public function pin(Request $request, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
           'id' => $postId,
           'author' => $this->getUser()->getId(),
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([
                'id' => "Post not found"
            ], null, 404);
        }

        $post->setPrivate(false);

        $pin = $em->getRepository(Pin::class)->findOneBy([
            'userId' => $this->getUser()->getId(),
        ]);

        if($pin INSTANCEOF Pin){
            $em->remove($pin);
        }
        $em->flush();

        $pin = new Pin();
        $pin->setUserId($this->getUser()->getId());
        $pin->setPostId($postId);

        $em->persist($pin);
        $em->flush();

        return $this->respond([
            'pin' => $pin->toArray(),
        ]);
    }

    /**
     * @Route("/post/pin", name="unpin_post", methods={"DELETE"})
     */
    public function unpin(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $pin = $em->getRepository(Pin::class)->findOneBy([
            'userId' => $this->getUser()->getId(),
        ]);

        if($pin INSTANCEOF Pin){
            $em->remove($pin);
            $em->flush();
        }

        return $this->respond([]);
    }
}