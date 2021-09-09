<?php
namespace App\Controller\Actions\Post;

use App\Controller\ApiController;
use App\Entity\Media\Attachment;
use App\Entity\Media\File;
use App\Entity\Post\Favorite;
use App\Entity\Post\Post;
use App\Entity\User\User;
use App\Service\Content\Resource;
use App\Service\Content\Resources\Image;
use App\Service\Post\Delete;
use App\Service\Post\Tags;
use App\Service\Truncate;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;
use voku\helper\AntiXSS;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteController extends ApiController
{
    /**
     * @Route("/post/{postId}", name="post_delete", methods={"DELETE"})
     */
    public function post(Request $request, Delete $delete, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId
        ]);

        if(
            !$post INSTANCEOF Post || $post->getAuthor()->getId() !== $this->getUser()->getId()
            || (count($post->getAttachments()) === 0 && is_null($post->getBody()) &&
                !is_null($post->getPoll()) && !is_null($post->getMagnet()))
        ){
            return $this->respondWithErrors([
                'id' => 'Post not found.'
            ], null, 404);
        }

        if($delete->delete($postId)){
            return $this->respond([]);
        }

        return $this->respondWithErrors([], 'There was an error.', 400);
    }
}
