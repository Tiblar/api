<?php

namespace App\Controller\Actions\Post\Actions;

use App\Controller\ApiController;
use App\Entity\Post\Mention;
use App\Entity\Post\Post;
use App\Entity\Post\Reply;
use App\Entity\User\Addons\Notification;
use App\Entity\User\User;
use App\Service\Generator\Securimage;
use App\Service\Post\Parser;
use App\Service\Truncate;
use App\Service\User\Block;
use App\Service\User\Notifier;
use App\Service\User\Privacy;
use App\Structure\Post\SanitizedReply;
use App\Structure\User\SanitizedUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Security\Core\Security;
use voku\helper\AntiXSS;
use Symfony\Component\Routing\Annotation\Route;

class ReplyController extends ApiController
{
    /**
     * @Route("/post/reply/{postId}", name="reply_post", methods={"POST"})
     */
    public function replyCreate(
        Request $request, Block $blockService, Privacy $privacyService,
        Securimage $securimage, Security $security, Parser $parser, Notifier $notifier, $postId
    ){
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $securityId = $request->request->get('security_id');
        $securityCode = $request->request->get('security_code');

        if(!$user->isBoosted() && $securimage->isValid($securityId, $securityCode) == false){
            return $this->respondWithErrors([
                'captcha' => 'Invalid security code.'
            ], 'Security code error.');
        }

        $store = new SemaphoreStore();
        $factory = new LockFactory($store);

        $lock = $factory->createLock($user->getId() . 'reply');

        if(!$lock->acquire()) {
            return $this->respondWithErrors([], "Please try again.", 403);
        }
        $lastReply = $em->getRepository(Reply::class)->findBy([
            'author' => $this->getUser(),
        ], ['timestamp' => 'DESC']);

        $now = new \DateTime();
        if(!empty($lastReply) && $lastReply[0] INSTANCEOF Reply && $now->getTimestamp() - $lastReply[0]->getTimestamp()->getTimestamp() < 10){
            $seconds = (10 - ($now->getTimestamp() - $lastReply[0]->getTimestamp()->getTimestamp()));
            return $this->respondWithErrors(
                [],
                "Please wait "
                . $seconds .
                " second"
                . ($seconds !== 1 ? 's' : ''),
                429
            );
        }

        $body = $request->request->get('body');
        $parentId = $request->request->get('parent');

        $body = Truncate::truncate($body, 280, ['html' => true]);

        if(is_null($body) || ctype_space($body) || strlen($body) === 0){
            return $this->respondWithErrors([
                'body' => 'Body parameter required or is invalid.'
            ], null, 400);
        }

        $antiXSS = new AntiXSS();
        $body = $antiXSS->xss_clean($body);

        $body = htmlspecialchars($body);
        $body = Truncate::truncate($body, 280, ['html' => true]);

        if(strlen($body) === 0){
            return $this->respondWithErrors([
                'body' => 'Body parameter required or is invalid.'
            ], null, 400);
        }

        $mentions = $parser->mention($body, true);

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId
        ]);

        $block = $blockService->get($post->getAuthor()->getId());
        $privacy = $privacyService->get($post->getAuthor()->getId());

        if(
            !$privacy->getReply() &&
            !($security->isGranted("ROLE_USER") && $this->getUser()->getId() === $post->getAuthor()->getId())
        ){
            return $this->respondWithErrors([], "Replies have been disabled.", 403);
        }

        if(in_array($this->getUser()->getId(), $block->getBlocking())){
            return $this->respondWithErrors([
                'auth' => 'You have been blocked by this user.'
            ], "You have been blocked by this user.", 403);
        }

        if(
            !$post INSTANCEOF Post ||
            ($post->getReblog() INSTANCEOF Post && is_null($post->getBody()) && empty($post->getAttachments()))
        ){
            return $this->respondWithErrors([
                'id' => 'This post does not exist.'
            ], null, 404);
        }

        $parent = null;
        if(!is_null($parentId) && is_string($parentId)){
            $parent = $em->getRepository(Reply::class)->findOneBy([
                'id' => $parentId
            ]);
        }

        $reply = new Reply();
        $reply->setAuthor($user);
        $reply->setPost($post);
        $reply->setBody($body);

        if($parent INSTANCEOF Reply){
            if($parent->getDepth() >= 1){
                $reply->setDepth($parent->getDepth());
                $reply->setParent($parent->getParent());
                $parent->getParent()->addReply($reply);
            }else{
                $reply->setDepth($parent->getDepth() + 1);
                $reply->setParent($parent);
                $parent->addReply($reply);
            }
        }

        $notifier->add($user, $post->getAuthor()->getId(), Notification::$TYPE_REPLY, $post->getId());

        $em->persist($reply);

        $post->setRepliesCount($post->getRepliesCount() + 1);

        $mentionedUsers = [];
        foreach($mentions as $item){
            $mention = new Mention();
            $mention->setPostId($post->getId());
            $mention->setReplyId($reply->getId());
            $mention->setUserId($item['user_id']);
            $mention->setCauserId($user->getId());
            $mention->setIndices($item['indices']);

            $em->persist($mention);

            if(!in_array($mention->getUserId(), $mentionedUsers)){
                $notifier->add($user, $mention->getUserId(), Notification::$TYPE_REPLY_MENTION, $post->getId());
                $mentionedUsers[] = $mention->getUserId();
            }
        }

        $em->flush();

        $sanitizedUser = new SanitizedUser($user);

        $sanitized = new SanitizedReply($reply->toArray());
        $sanitized->setPostId($post->getId());
        if($reply->getParent() INSTANCEOF Reply){
            $sanitized->setParentId($reply->getParent()->getId());
        }
        $sanitized->setTimestamp($reply->getTimestamp());
        $sanitized->setAuthor($sanitizedUser);

        $lock->release();


        return $this->respond([
            'reply' => $sanitized->toArray(),
        ]);
    }

    /**
     * @Route("/post/reply/{replyId}", name="delete_reply", methods={"DELETE"})
     */
    public function replyDelete(Request $request, Notifier $notifier, $replyId)
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

        $reply = $em->getRepository(Reply::class)->findOneBy([
            'id' => $replyId,
            'author' => $this->getUser()->getId(),
        ]);

        if(!$reply INSTANCEOF Reply) {
            return $this->respondWithErrors([
                'id' => 'This reply does not exist.'
            ], null, 404);
        }

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $reply->getPost()->getId(),
        ]);

        if(!$post INSTANCEOF Post) {
            return $this->respondWithErrors([
                'id' => 'This post does not exist.'
            ], null, 404);
        }

        $post->setRepliesCount($post->getRepliesCount() - 1);

        if($post->getRepliesCount() < 0){
            $post->setRepliesCount(0);
        }

        if(count($reply->getReplies()) !== 0){
            $reply->setAuthor(null);
            $reply->setBody("[deleted]");
        }else{
            $em->remove($reply);
        }

        $em->flush();

        return $this->respond([]);
    }
}