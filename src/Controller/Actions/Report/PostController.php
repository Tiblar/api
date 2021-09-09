<?php

namespace App\Controller\Actions\Report;

use App\Controller\ApiController;
use App\Entity\Post\Post;
use App\Entity\Report\PostReport;
use App\Service\Generator\Securimage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends ApiController
{
    /**
     * @Route("/report/post/{postId}", name="report_post", methods={"POST"})
     */
    public function report(Request $request, Securimage $securimage, $postId)
    {
        $securityId = $request->request->get('security_id');
        $securityCode = $request->request->get('security_code');

        if($securimage->isValid($securityId, $securityCode) == false){
            return $this->respondWithErrors([
                'captcha' => 'Invalid security code.'
            ], 'Security code error.');
        }

        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respond([], "Post does not exist.", 404);
        }

        $report = $em->getRepository(PostReport::class)->findOneBy([
            'postId' => $post->getId(),
        ]);

        if($report INSTANCEOF PostReport){
            return $this->respond([]);
        }

        $report = new PostReport();
        $report->setUserId($this->getUser()->getId());
        $report->setPostId($post->getId());

        $em->persist($report);
        $em->flush();

        return $this->respond([]);
    }
}