<?php
namespace App\Service\Post;

use App\Entity\Post\Tag;
use App\Entity\Post\TagList;
use Doctrine\ORM\EntityManagerInterface;

class Tags {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var int
     */
    private $maxTags;

    /**
     * @var int
     */
    private $maxTagLength;

    public function __construct(EntityManagerInterface $em, $maxTags, $maxTagLength)
    {
        $this->em = $em;
        $this->maxTags = $maxTags;
        $this->maxTagLength = $maxTagLength;
    }

    /**
     * @param array $tags
     * @param string $postId
     * @param string $userId
     */
    public function addTags(array $tags, string $postId, string $userId): void
    {
        $tagRepository = $this->em->getRepository(Tag::class);

        foreach($tags as $key=>$tag) {
            $split = explode('#', $tag);
            if(count($split) > 1){
                unset($tags[$key]);

                foreach($split as $item) {
                    $tags[] = trim($item, ",");
                }
            }
        }

        $tags = array_slice($tags, 0, $this->maxTags);

        array_walk($tags, [$this, "cut_tags"]);

        $tags = array_unique($tags);

        $track = [];
        foreach($tags as $title){
            $tag = $tagRepository->findOneBy([
                'title' => $title,
            ]);

            if(!$tag INSTANCEOF Tag){
                $tag = new Tag();

                $tag->setTitle($title);
                $tag->setCount(0);
                $tag->setNsfw(false);

                $this->em->persist($tag);
            }

            $tag->setCount($tag->getCount() + 1);

            $tagList = new TagList();

            $tagList->setTitle($title);
            $tagList->setPost($postId);
            $tagList->setUserId($userId);
            $tagList->setTag($tag->getId());

            $this->em->persist($tagList);

            $track[] = $tag;
        }

        $this->em->flush();
    }

    /**
     * @param array $tags
     */
    public function removeTags(array $tags)
    {

    }

    private function cut_tags(&$tag) {
        $tag = trim(substr($tag, 0, $this->maxTagLength), ", ");
    }
}
