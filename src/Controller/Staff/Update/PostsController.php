<?php

namespace App\Controller\Staff\Update;

use App\Controller\ApiController;
use App\Entity\Post\Post;
use App\Service\Post\Delete;
use App\Service\SpamFilter\SpamFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends ApiController
{
    /**
     * @Route("/update/post/{postId}/nsfw", name="staff_api_update_nsfw", methods={"PATCH"})
     */
    public function nsfw(Request $request, SpamFilter $spamFilter, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId,
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([], null, 404);
        }

        $post->setNsfw(true);

        $em->flush();

        $spamFilter->learn($post->getBody(), $post->getIpAddress(), SpamFilter::HAM);

        return $this->respond([]);
    }

    /**
     * @Route("/update/post/{postId}/sfw", name="staff_api_update_sfw", methods={"PATCH"})
     */
    public function sfw(Request $request, SpamFilter $spamFilter, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId,
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([], null, 404);
        }

        $post->setNsfw(false);

        $em->flush();

        $spamFilter->learn($post->getBody(), $post->getIpAddress(), SpamFilter::HAM);

        return $this->respond([]);
    }

    /**
     * @Route("/update/post/{postId}/mark-delete", name="staff_api_update_mark_delete", methods={"PATCH"})
     */
    public function markDelete(Request $request, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId,
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([], null, 404);
        }

        $post->setMarkDelete(true);

        $em->flush();

        return $this->respond([]);
    }

    /**
     * @Route("/update/post/{postId}/mark-spam", name="staff_api_update_mark_spam", methods={"POST"})
     */
    public function markSpam(Request $request, SpamFilter $spamFilter, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId,
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([], null, 404);
        }

        $post->setMarkDelete(true);

        $em->flush();

        $spamFilter->learn($post->getBody(), $post->getIpAddress(), SpamFilter::SPAM);

        return $this->respond([]);
    }

    /**
     * @Route("/update/post/{postId}/mark-delete", name="staff_api_update_unmark_delete", methods={"DELETE"})
     */
    public function unmarkDelete(Request $request, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId,
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([], null, 404);
        }

        $post->setMarkDelete(false);

        $em->flush();

        return $this->respond([]);
    }

    /**
     * @Route("/update/post/{postId}/delete", name="staff_api_update_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Delete $delete, $postId)
    {
        $delete->delete($postId);

        return $this->respond([]);
    }

    /**
     * @Route("/update/post/{postId}/spam", name="staff_api_update_spam", methods={"POST"})
     */
    public function spam(Request $request, SpamFilter $spamFilter, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $unlearn = filter_var($request->request->get('unlearn'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId,
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([], null, 404);
        }

        $post->setSpam(true);
        $post->setMarkDelete(true);

        $em->flush();

        if($unlearn === true){
            $spamFilter->unlearn($post->getBody(), SpamFilter::HAM);
        }

        $spamFilter->learn($post->getBody(), $post->getIpAddress(), SpamFilter::SPAM);

        return $this->respond([]);
    }

    /**
     * @Route("/update/post/{postId}/spam", name="staff_api_update_not_spam", methods={"DELETE"})
     */
    public function ham(Request $request, SpamFilter $spamFilter, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $unlearn = filter_var($request->request->get('unlearn'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId,
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([], null, 404);
        }

        $post->setSpam(false);
        $post->setMarkDelete(false);

        $em->flush();

        if($unlearn === true){
            $spamFilter->unlearn($post->getBody(), SpamFilter::SPAM);
        }

        $spamFilter->learn($post->getBody(), $post->getIpAddress(), SpamFilter::HAM);

        return $this->respond([]);
    }
}