<?php
namespace App\Service\Post\Retrieve;

use App\Entity\Media\Attachment;
use App\Entity\Media\Magnet;
use App\Entity\Media\Poll;
use App\Entity\User\User;
use App\Structure\Media\SanitizedPoll;
use App\Structure\Post\SanitizedMention;
use App\Structure\Post\SanitizedPost;
use App\Structure\Post\SanitizedReply;
use App\Structure\User\BlockStructure;
use App\Structure\User\FollowStructure;
use App\Structure\User\SanitizedUser;
use App\Service\Transcoding\Turntable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Formatter {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var AddonsBuilder
     */
    private $addonsBuilder;

    private $domain;

    private $bucket;

    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
        $this->domain = $params->get('s3')['domain'];
        $this->bucket = $params->get('s3')['bucket'];
    }

    public function posts(array $postsIds, array $addons = [])
    {
        $follow = isset($addons['follow']) && $addons['follow'] INSTANCEOF FollowStructure
            ? $addons['follow'] : null;
        $block = isset($addons['block']) && $addons['block'] INSTANCEOF BlockStructure
            ? $addons['block'] : null;

        $posts = isset($addons['posts']) ? $addons['posts'] : null;
        $users = isset($addons['users']) ? $addons['users'] : null;
        $polls = isset($addons['polls']) ? $addons['polls'] : null;
        $mentions = isset($addons['mentions']) ? $addons['mentions'] : null;
        $tags = isset($addons['tags']) ? $addons['tags'] : null;
        $reblogsIds = isset($addons['reblogsIds']) ? $addons['reblogsIds'] : null;
        $favoritesIds = isset($addons['favoritesIds']) ? $addons['favoritesIds'] : null;
        $pinIds = isset($addons['pinIds']) ? $addons['pinIds'] : null;
        $views = isset($addons['views']) ? $addons['views'] : null;

        if(is_null($posts)){
            $posts = $this->addonsBuilder->getPosts($postsIds);
        }

        if(is_null($views)){
            $views = $this->addonsBuilder->getViews($this->addonsBuilder->getPostIds($posts));
        }

        if(is_null($users)){
            $users = $this->addonsBuilder->getUsers($this->addonsBuilder->getAuthorIds($posts));
        }

        if(is_null($mentions)){
            $mentions = $this->addonsBuilder->getMentions($this->addonsBuilder->getPostIds($posts));
        }

        if(is_null($tags)){
            $tags = $this->addonsBuilder->getTags($this->addonsBuilder->getPostIds($posts));
        }

        if(is_null($reblogsIds)){
            $reblogsIds = $this->addonsBuilder->getReblogsIds();
        }

        if(is_null($favoritesIds)){
            $favoritesIds = $this->addonsBuilder->getFavoritesIds();
        }

        if(is_null($pinIds)){
            $pinIds = $this->addonsBuilder->getPinsIds($this->addonsBuilder->getPostIds($posts));
        }

        if(is_null($polls)){
            $polls = $this->em->getRepository(Poll::class)
                ->findSanitizedPolls($this->addonsBuilder->getPostIds($posts), $this->addonsBuilder->getUserId());
        }

        $sanitized = [];
        foreach($posts as $post){

            if(isset($post['attachments'])){
                foreach($post['attachments'] as &$attachment){

                    $attachment['file']['url'] = '//' . $this->domain . '/' . $this->bucket . '/'.  $attachment['file']['hash'] . '.' . $attachment['file']['extension'];

                    if(isset($attachment['thumbnails']) && is_array($attachment['thumbnails'])){
                        foreach($attachment['thumbnails'] as &$thumbnail){
                            $thumbnail['file']['url'] = '//' . $this->domain . '/' . $this->bucket . '/'.  $thumbnail['file']['hash'] . '.' . $thumbnail['file']['extension'];
                        }
                    }

                    $ext = pathinfo($attachment['file']['url'], PATHINFO_EXTENSION);
                    //print_r($post);
                    //if (!empty($post['videoCategory']) && count($post['videoCategory'])) {
                    //if (strtolower($ext) !== 'zip') {

                    // as long as we have a hash, lets process it...
                    if (!empty($attachment['file']['hash'])) {
                      $file = $attachment['file']['hash'] . '.' . $ext;
                      $tc = new Turntable();
                      $result = $tc->transcode($file);
                      $attachment['available_transcoding'] = json_decode($result, true);
                    }
                    //}
                    //}
                }
            }

            $sanitizedPost = new SanitizedPost($post);

            foreach($users as $user){
                if(!$user instanceof SanitizedUser){
                    continue;
                }

                if(!isset($post['author']) || !isset($post['author']['id'])){
                    continue;
                }

                if($user->getId() === $post['author']['id']){
                    $sanitizedPost->setAuthor($user);
                }
            }

            foreach($views as $view){
                if($view['postId'] === $post['id']){
                    $sanitizedPost->setViews($view['views']);
                }
            }

            foreach($mentions as $mention){
                if($mention->getPostId() === $post['id']){
                    $sanitizedPost->addMention($mention->toArray());
                }
            }

            foreach($tags as $tag){
                if($tag['post'] === $post['id']){
                    $sanitizedPost->addTag($tag);
                }
            }

            if(in_array($sanitizedPost->getId(), $reblogsIds)){
                $sanitizedPost->setReblogged(true);
            }

            if(in_array($sanitizedPost->getId(), $favoritesIds)){
                $sanitizedPost->setFavorited(true);
            }

            if(in_array($sanitizedPost->getId(), $pinIds)){
                $sanitizedPost->setPinned(true);
            }

            if($this->addonsBuilder->isStaff()){
                $sanitizedPost->setSpam($post['spam']);
            }

            foreach($polls as $poll){
                if($poll->getPostId() === $post['id']){
                    $sanitizedPost->setPoll($poll);
                }
            }

            if(isset($post['reblog']) && !is_null($post['reblog'])){
                $reblog = $this->posts([], [
                    'posts' => [$post['reblog']],
                    'users' => $users,
                    'follow' => $follow,
                    'block' => $block,
                    'polls' => $polls,
                    'mentions' => $mentions,
                    'tags' => $tags,
                    'reblogsIds' => $reblogsIds,
                    'favoritesIds' => $favoritesIds,
                    'pinIds' => $pinIds,
                ]);

                if(count($reblog) && $reblog[0] INSTANCEOF SanitizedPost){
                    $sanitizedPost->setReblog($reblog[0]);
                }
            }

            if(
                $sanitizedPost->isPrivate() &&
                $sanitizedPost->getAuthor()->getId() !== $this->addonsBuilder->getUserId() &&
                !$this->addonsBuilder->isStaff()
            ){
                continue;
            }

            $sanitized[] = $sanitizedPost;
        }

        return $sanitized;
    }

    public function replies(array $replies, $postId, $parentId = null, $users = null, $block = null, $mentions = null)
    {
        if(is_null($block)){
            $block = $this->addonsBuilder->getBlock();
        }

        $children = array_column($replies, "children");

        $mergedReplies = $replies;
        foreach($children as $child){
            if(empty($child)){
                continue;
            }

            $mergedReplies = array_merge($mergedReplies, $child);
        }

        $userIds = $this->addonsBuilder->getAuthorIds($mergedReplies);

        if(is_null($mentions)) {
            $mentions = $this->addonsBuilder->getReplyMentions($postId);
            $userIds = array_merge($userIds, array_column($mentions, 'userId'));
        }

        $userIds = array_unique($userIds);

        if(is_null($users)){
            $users = $this->em->getRepository(User::class)
                ->findSanitizedUsers($userIds);
        }

        $sanitized = [];
        foreach($replies as $reply){
            $sanitizedReply = new SanitizedReply($reply);

            $sanitizedReply->setPostId($postId);

            if(!is_null($parentId)){
                $sanitizedReply->setParentId($parentId);
            }

            foreach($users as $user){
                if(is_null($reply['author']) || !isset($reply['author']['id'])){
                    $sanitizedReply->setAuthor(null);
                    break;
                }elseif($user->getId() === $reply['author']['id']){
                    $sanitizedReply->setAuthor($user);
                    break;
                }else{
                    $sanitizedReply->setAuthor(null);
                }
            }

            if(
                !is_null($sanitizedReply->getAuthor()) && in_array($sanitizedReply->getAuthor()->getId(), $block->getBlocking())
            ){
                continue;
            }

            foreach($mentions as $mention){
                if($mention['replyId'] === $reply['id']){
                    foreach($users as $user){
                        if(!isset($reply['author']['id'])){
                            throw new \Exception("Author ID not found.");
                        }

                        if($user->getId() === $mention['userId']){
                            $sanitizedMention = new SanitizedMention([
                                'id' => $mention['postId'],
                                'user' => $user,
                                'indices' => json_decode($mention['indices']),
                            ]);

                            $sanitizedReply->addMention($sanitizedMention->toArray());
                        }
                    }
                }
            }

            if(isset($reply['children']) && !is_null($reply['children'])){
                foreach($reply['children'] as $child){
                    $sanitizedChild = $this->replies([$child], $postId, $reply['id'], $users, $block, $mentions);

                    if(count($sanitizedChild) && $sanitizedChild[0] INSTANCEOF SanitizedReply){
                        $sanitizedReply->addReply($sanitizedChild[0]);
                    }
                }
            }

            $sanitized[] = $sanitizedReply;
        }

        foreach($sanitized as $reply){
            $replies = $reply->getReplies();

            usort($replies, function($a, $b) {
                return strcmp($a->getTimestamp()->format('c'), $b->getTimestamp()->format('c'));
            });

            $reply->setReplies($replies);
        }

        return $sanitized;
    }
}