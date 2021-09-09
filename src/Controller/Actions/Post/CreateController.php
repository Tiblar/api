<?php
namespace App\Controller\Actions\Post;

use App\Controller\ApiController;
use App\Entity\Media\Attachment;
use App\Entity\Media\File;
use App\Entity\Media\FileInit;
use App\Entity\Media\Magnet;
use App\Entity\Media\Poll;
use App\Entity\Media\Thumbnail;
use App\Entity\Post\Mention;
use App\Entity\Post\Post;
use App\Entity\User\Addons\Notification;
use App\Entity\User\User;
use App\Service\Content\Resource;
use App\Service\Content\Resources\Image;
use App\Service\Content\Resources\ResourceInterface;
use App\Service\Generator\Securimage;
use App\Service\Post\Parser;
use App\Service\Post\Tags;
use App\Service\Post\WaveConverter;
use App\Service\SpamFilter\SpamFilter;
use App\Service\Truncate;
use App\Service\User\Notifier;
use Doctrine\DBAL\Driver\Exception;
use DOMDocument;
use DOMXPath;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use voku\helper\AntiXSS;
use FFMpeg;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateController extends ApiController
{
    /**
     * @Route("/post", name="post_create", methods={"POST"})
     */
    public function post(
        Request $request, Tags $tagsService, Resource $resourceFactory, WaveConverter $waveConverter,
        Securimage $securimage, Parser $parser, Notifier $notifier, SpamFilter $spamFilter
    ) {
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

        $uploadedFiles = !is_null($request->files->get('files')) ? $request->files->get('files') : [];
        $largeFiles = !is_null($request->request->get('files')) ? $request->request->get('files') : [];
        $title = $request->request->get('title');
        $body = $request->request->get('body');
        $rows = !is_null($request->request->get('rows')) ? array_map('intval', $request->request->get('rows')) : [];
        $tags = !is_null($request->request->get('tags')) ? $request->request->get('tags') : [];
        $nsfw = filter_var($request->request->get('nsfw'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $private = filter_var($request->request->get('private'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $followersOnly = filter_var($request->request->get('followers_only'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $reblogId = $request->request->get('reblog');
        $categories =
            !is_null($request->request->get('category')) && is_array($request->request->get('category'))
                ? array_slice($request->request->get('category'), 0, 2) : [];
        $thumbnail = $request->files->get('thumbnail');

        $question = $request->request->get('poll_question');
        $options = !is_null($request->request->get('poll_options')) ? array_map(function ($option) {
            return is_array($option) ? "" : strval($option);
        }, $request->request->get('poll_options')) : [];

        $magnetURL = $request->request->get('magnet');

        $clientIp = $request->headers->get("X-Forwarded-For-Formerly-Chucks");

        $spamRating = 0;
        if(is_string($body)){
            $spamRating = $spamFilter->classify($body, $clientIp);
        }

        $isSpam = false;
        if($spamRating >= 0.85){
            $isSpam = true;
        }

        // Mark all Indian, Pakistan, Bangladesh posts as spam
        if(filter_var($clientIp, FILTER_VALIDATE_IP)){
            $country = shell_exec("whois " . escapeshellarg($clientIp) . " | grep -iE ^country: | awk '{print $2}'");
            $countries = preg_split('/\s+/', $country);

            if(!empty(array_intersect($countries, ["BD", "IN", "PK"]))){
                $isSpam = true;
            }
        }

        $reblog = $em->getRepository(Post::class)->findOneBy([
            'id' => $reblogId
        ]);

        if($user->getStorage() > $user->getStorageLimit() && (count($uploadedFiles) || count($largeFiles))){
            return $this->respondWithErrors([
                'storage' => 'Storage limit hit.'
            ], 'You are out of storage space. Please upgrade your account to increase storage limit.');
        }

        if(!is_null($reblogId) && !$reblog INSTANCEOF Post){
            return $this->respondWithErrors([
                'reblog' => 'This post does not exist'
            ], 'Post error.', 404);
        }

        if($reblog INSTANCEOF Post && !is_null($question)){
            return $this->respondWithErrors([
                'reblog' => 'You cannot reblog this post with a comment.'
            ], 'Post error.', 400);
        }

        if(!is_null($question)){
            $uploadedFiles = [];
            $body = null;
            $title = null;
            $magnet = null;
        }

        if(!is_null($magnetURL)){
            $uploadedFiles = [];
            $question = null;
            $options = [];
        }

        foreach($largeFiles as &$file){
            $file = json_decode($file, true);

            if(!isset($file['file_id']) || !isset($file['row']) || !isset($file['col'])
            ){
                return $this->respondWithErrors([
                    'files' => 'Invalid large file, missing parameter.'
                ], 'Post error.', 400);
            }

            $file['row'] = intval($file['row']);
            $file['col'] = intval($file['col']);
        }

        if(count($largeFiles) > 1){
            $largeFiles = [$largeFiles[0]];
        }

        if(!empty($largeFiles) && !empty($uploadedFiles)){
            $uploadedFiles = [];
        }

        // Should have a body or uploaded files or poll or magnet
        if(
            (is_null($body) || empty(strip_tags($body)) || $this->stripHTMLAndSpace($body) === 0)
            && (empty($uploadedFiles) && empty($largeFiles))
            && (
                empty($question) || strlen($this->stripHTMLAndSpace($question)) === 0 ||
                !isset($options[0]) || strlen($this->stripHTMLAndSpace($options[0])) === 0 ||
                !isset($options[1]) || strlen($this->stripHTMLAndSpace($options[1])) === 0
            )
            && (empty($magnetURL) || !$this->isValidMagnet($magnetURL))
            && (is_null($question) && is_null($reblogId))
        ){
            return $this->respondWithErrors([
                'content' => 'You need a poll, magnet, body or file.'
            ], 'Post error.');
        }

        // Validate rows for uploaded files
        $this->validateRows($rows);

        $author = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$author INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        if($title){
            $title = substr($title, 0, $this->getParameter('max_title_length'));
        }

        $antiXSS = new AntiXSS();

        if(!is_null($body)){
            $body = strip_tags($body, "<a><p><strong><ol><ul><li><em><u><h1><h2><h3><br>");

            libxml_use_internal_errors(true);

            $dom = new DOMDocument;

            @$dom->loadHTML(mb_convert_encoding("<div>" . $body . "</div>", 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $xpath = new DOMXPath($dom);
            $nodes = $xpath->query('//@*');
            foreach($nodes as $node){
                if($node->nodeName != "src" && $node->nodeName != "href") {
                    $node->parentNode->removeAttribute($node->nodeName);
                }
            }

            $body = strip_tags($dom->saveHTML($dom->documentElement), "<a><p><strong><ol><ul><li><em><u><h1><h2><h3><br>");
            $body = Truncate::truncate($antiXSS->xss_clean($body), $this->getParameter('max_body_length'), ['html' => true]);

            if($this->stripHTMLAndSpace($body) === 0){
                return $this->respondWithErrors([
                    'content' => 'Body parameter required or is invalid.'
                ], 'Post error.');
            }
        }

        $post = new Post();
        $post->setAuthor($author);
        $post->setTitle($title);
        $post->setBody($body);
        $post->setNsfw(is_null($nsfw) ? false : $nsfw);
        $post->setPrivate(is_null($private) ? false : $private);
        $post->setFollowersOnly(is_null($followersOnly) ? false : $followersOnly);
        $post->setIpAddress($clientIp);
        $post->setSpam($isSpam);

        if($reblog INSTANCEOF Post){
            $post->setReblog($reblog);
            $reblog->setReblogsCount($reblog->getReblogsCount() + 1);
        }

        $em->persist($post);

        $size = 0;
        $postType = null;

        // For uploaded files that are not large files
        foreach($uploadedFiles as $i => $uploadedFile){
            $resource = $resourceFactory->getFile($uploadedFile);

            if(!is_null($postType) && $postType != $resource->getPostType()){
                return $this->respondWithErrors([
                    'content' => 'Mismatched file types.'
                ], 'Post error.');
            }

            if(is_null($postType)){
                $postType = $resource->getPostType();
            }

            if($postType === Resource::POST_IMAGE && $i > 9){
               break;
            }

            if($postType !== Resource::POST_IMAGE && $i > 0){
                break;
            }

            if(
                $postType === Resource::POST_IMAGE &&
                $uploadedFile->getSize() / 1000 / 1000 > $this->getParameter('file_sizes')['image']
            ){
                return $this->respondWithErrors([
                    'files' => "You included an image file greater than " . $this->getParameter('file_sizes')['image'] . " mb."
                ], 'Post error.');
            }

            if(
                $postType === Resource::POST_VIDEO &&
                $uploadedFile->getSize() / 1000 / 1000 > $this->getParameter('file_sizes')['video']
            ){
                return $this->respondWithErrors([
                    'files' => "You included a video file greater than " . $this->getParameter('file_sizes')['image'] . " mb."
                ], 'Post error.');
            }

            if(
                $postType === Resource::POST_AUDIO &&
                $uploadedFile->getSize() / 1000 / 1000 > $this->getParameter('file_sizes')['audio']
            ){
                return $this->respondWithErrors([
                    'files' => "You included an audio file greater than " . $this->getParameter('file_sizes')['audio'] . " mb."
                ], 'Post error.');
            }

            if(
                $postType === Resource::POST_PDF &&
                $uploadedFile->getSize() / 1000 / 1000 > $this->getParameter('file_sizes')['pdf']
            ){
                return $this->respondWithErrors([
                    'files' => "You included a pdf file greater than " . $this->getParameter('file_sizes')['pdf'] . " mb."
                ], 'Post error.');
            }

            if(
                $postType === Resource::POST_FILE &&
                $uploadedFile->getSize() / 1000 / 1000 > $this->getParameter('file_sizes')['file']
            ){
                return $this->respondWithErrors([
                    'files' => "You included a file greater than " . $this->getParameter('file_sizes')['file'] . " mb."
                ], 'Post error.');
            }

            if($resource->getPostType() === Resource::POST_AUDIO){
                $waveConverter->create($uploadedFile);
            }

            $thumbResource = null;
            if($postType === Resource::POST_VIDEO){
                $selectedCategories = $em->createQueryBuilder()
                    ->select('c')
                    ->from('App:Video\Category', 'c')
                    ->where('c.id IN (:categories)')
                    ->setParameter('categories', $categories)
                    ->getQuery()
                    ->getResult();

                foreach($selectedCategories as $category){
                    $post->addCategory($category);
                }

                if($thumbnail instanceof UploadedFile){
                    $thumbResource = $resourceFactory->getFile($thumbnail);

                    if($thumbnail->getSize() / 1000 / 1000 > 5){
                        return $this->respondWithErrors([
                            'thumbnail' => "You included a thumbnail greater than 5 mb."
                        ], 'Post error.');
                    }

                    if(!($thumbResource instanceof Image)){
                        return $this->respondWithErrors([
                            'thumbnail' => "Must be an image."
                        ], 'Post error.');
                    }

                    if($thumbResource->getWidth() !== 1280 || $thumbResource->getHeight() !== 720){
                        return $this->respondWithErrors([
                            'thumbnail' => "Image must be 1280x720."
                        ], 'Post error.');
                    }
                }else{
                    try{
                        $ffprobe = FFMpeg\FFProbe::create([
                            'ffmpeg.binaries'  => exec('which ffmpeg'),
                            'ffprobe.binaries' => exec('which ffprobe')
                        ]);
                    }catch (\Exception $e) {
                        $ffprobe = FFMpeg\FFProbe::create([
                            'ffmpeg.binaries'  => exec('type -P ffmpeg'),
                            'ffprobe.binaries' => exec('type -P ffprobe')
                        ]);
                    }

                    $duration = $ffprobe
                        ->format($uploadedFile->getRealPath())
                        ->get('duration');

                    $sec = 0;
                    foreach (array_reverse(explode(':', $duration)) as $k => $v) $sec += pow(60, $k) * $v;

                    try{
                        $ffmpeg = FFMpeg\FFMpeg::create([
                            'ffmpeg.binaries'  => exec('which ffmpeg'),
                            'ffprobe.binaries' => exec('which ffprobe')
                        ]);
                    }catch (\Exception $e) {
                        $ffmpeg = FFMpeg\FFMpeg::create([
                            'ffmpeg.binaries'  => exec('type -P ffmpeg'),
                            'ffprobe.binaries' => exec('type -P ffprobe')
                        ]);
                    }

                    $randSec = rand(0, $sec);

                    $tmpHandle = tmpfile();
                    $tmpFile = stream_get_meta_data($tmpHandle)['uri'];

                    $ffmpeg
                        ->open($uploadedFile->getRealPath())
                        ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($randSec))
                        ->addFilter(new FFMpeg\Filters\Frame\CustomFrameFilter('scale=1280x720'))
                        ->save($tmpFile);

                    $thumbnailFile = new UploadedFile($tmpFile, "thumbnail.png", "image/png");
                    $thumbResource = $resourceFactory->getFile($thumbnailFile);
                }
            }

            unset($file);
            $file = $em->getRepository(File::class)->findOneBy([
                'hash' => $resource->getHash()
            ]);

            if(!$file INSTANCEOF File){
                $file = new File();
                $file->setURL($resource->upload());
                $file->setHash($resource->getHash());
                $file->setHashName($resource->getHashName());
                $file->setFileSize($resource->getFileSize());
                $file->setExtension($resource->getExtension());

                if($resource INSTANCEOF Image){
                    $file->setWidth($resource->getWidth());
                    $file->setHeight($resource->getHeight());
                }

                $em->persist($file);
            }

            if($postType === Resource::POST_VIDEO && is_null($file->getDuration())){
                try{
                    $ffprobe = FFMpeg\FFProbe::create([
                        'ffmpeg.binaries'  => exec('which ffmpeg'),
                        'ffprobe.binaries' => exec('which ffprobe')
                    ]);
                }catch (\Exception $e) {
                    $ffprobe = FFMpeg\FFProbe::create([
                        'ffmpeg.binaries'  => exec('type -P ffmpeg'),
                        'ffprobe.binaries' => exec('type -P ffprobe')
                    ]);
                }

                $duration = $ffprobe
                    ->format($uploadedFile->getRealPath())
                    ->get('duration');

                $file->setDuration(floor($duration));
            }

            $attachment = new Attachment();

            if($thumbResource){
                $this->createThumbnails($thumbResource, $attachment);
            }

            $attachment->setFile($file);
            $attachment->setOriginalName($uploadedFile->getClientOriginalName());
            if(isset($rows[$i])){
                $attachment->setRow($rows[$i]);
            }else{
                $attachment->setRow($i);
            }
            $attachment->setPost($post);

            $em->persist($attachment);

            $post->addAttachment($attachment);


            $size += $file->getFileSize();
        }

        // For uploaded large files
        foreach($largeFiles as $largeFile){
            $fileInit = $em->getRepository(FileInit::class)->findOneBy([
                'id' => $largeFile['file_id'],
                'status' => FileInit::$FILE_INIT_FINISHED
            ]);

            if(!($fileInit instanceof FileInit)){
                return $this->respondWithErrors([
                    'file_id' => "This file does not exist."
                ], 'Post error.');
            }

            $file = $em->getRepository(File::class)->findOneBy([
                'id' => $fileInit->getFileId()
            ]);

            if(!($file instanceof File)){
                return $this->respondWithErrors([
                    'file_id' => "This file does not exist."
                ], 'Post error.');
            }

            $ext = pathinfo($fileInit->getOriginalName(), PATHINFO_EXTENSION);

            $fileTypes = $this->getParameter('file_types');

            $foundType = null;

            foreach($fileTypes as $type => $exts){
                foreach($exts as $typeExt){
                    if($typeExt === $ext){
                        $foundType = $type;
                        break;
                    }
                }
            }

            if($foundType === "video"){
                try{
                    $ffprobe = FFMpeg\FFProbe::create([
                        'ffmpeg.binaries'  => exec('which ffmpeg'),
                        'ffprobe.binaries' => exec('which ffprobe')
                    ]);
                }catch (\Exception $e) {
                    $ffprobe = FFMpeg\FFProbe::create([
                        'ffmpeg.binaries'  => exec('type -P ffmpeg'),
                        'ffprobe.binaries' => exec('type -P ffprobe')
                    ]);
                }

                $duration = $ffprobe
                    ->format("https:" . $file->getURL())
                    ->get('duration');

                $file->setDuration(floor($duration));

                $selectedCategories = $em->createQueryBuilder()
                    ->select('c')
                    ->from('App:Video\Category', 'c')
                    ->where('c.id IN (:categories)')
                    ->setParameter('categories', $categories)
                    ->getQuery()
                    ->getResult();

                foreach($selectedCategories as $category){
                    $post->addCategory($category);
                }
            }

            $thumbResource = null;
            if($foundType === "video"){
                if($thumbnail instanceof UploadedFile){
                    $thumbResource = $resourceFactory->getFile($thumbnail);

                    if($thumbnail->getSize() / 1000 / 1000 > 5){
                        return $this->respondWithErrors([
                            'thumbnail' => "You included a thumbnail greater than 5 mb."
                        ], 'Post error.');
                    }

                    if(!($thumbResource instanceof Image)){
                        return $this->respondWithErrors([
                            'thumbnail' => "Must be an image."
                        ], 'Post error.');
                    }

                    if($thumbResource->getWidth() !== 1280 || $thumbResource->getHeight() !== 720){
                        return $this->respondWithErrors([
                            'thumbnail' => "Image must be 1280x720."
                        ], 'Post error.');
                    }
                }else{
                    try{
                        $ffprobe = FFMpeg\FFProbe::create([
                            'ffmpeg.binaries'  => exec('which ffmpeg'),
                            'ffprobe.binaries' => exec('which ffprobe')
                        ]);
                    }catch (\Exception $e) {
                        $ffprobe = FFMpeg\FFProbe::create([
                            'ffmpeg.binaries'  => exec('type -P ffmpeg'),
                            'ffprobe.binaries' => exec('type -P ffprobe')
                        ]);
                    }

                    $duration = $ffprobe
                        ->format("https:" . $file->getURL())
                        ->get('duration');

                    $sec = 0;
                    foreach (array_reverse(explode(':', $duration)) as $k => $v) $sec += pow(60, $k) * $v;

                    if($sec > 5){
                        $sec = 5;
                    }

                    try{
                        $ffmpeg = FFMpeg\FFMpeg::create([
                            'ffmpeg.binaries'  => exec('which ffmpeg'),
                            'ffprobe.binaries' => exec('which ffprobe')
                        ]);
                    }catch (\Exception $e) {
                        $ffmpeg = FFMpeg\FFMpeg::create([
                            'ffmpeg.binaries'  => exec('type -P ffmpeg'),
                            'ffprobe.binaries' => exec('type -P ffprobe')
                        ]);
                    }

                    $randSec = rand(0, $sec);

                    $tmpHandle = tmpfile();
                    $tmpFile = stream_get_meta_data($tmpHandle)['uri'];

                    $ffmpeg
                        ->open("https:" . $file->getURL())
                        ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($randSec))
                        ->addFilter(new FFMpeg\Filters\Frame\CustomFrameFilter('scale=1280x720'))
                        ->save($tmpFile);

                    $thumbnailFile = new UploadedFile($tmpFile, "thumbnail.png", "image/png");
                    $thumbResource = $resourceFactory->getFile($thumbnailFile);
                }
            }

            $attachment = new Attachment();

            if($thumbResource){
                $this->createThumbnails($thumbResource, $attachment);
            }

            $attachment->setFile($file);
            $attachment->setOriginalName($fileInit->getOriginalName());

            $attachment->setRow(0);
            $attachment->setPost($post);

            $em->persist($attachment);
            $em->remove($fileInit);

            $post->addAttachment($attachment);
        }

        if(
            !empty($question) && strlen($this->stripHTMLAndSpace($question)) > 0 ||
            isset($options[0]) && strlen($this->stripHTMLAndSpace($options[0])) > 0 ||
            isset($options[1]) && strlen($this->stripHTMLAndSpace($options[1])) > 0
        ){
            $poll = new Poll();
            $poll->setPostId($post->getId());
            $poll->setQuestion($question);

            if(isset($options[0]) && strlen($this->stripHTMLAndSpace($options[0])) > 0){
                $poll->setOptionOne($options[0]);
            }

            if(isset($options[1]) && strlen($this->stripHTMLAndSpace($options[1])) > 0){
                $poll->setOptionTwo($options[1]);
            }

            if(isset($options[2]) && strlen($this->stripHTMLAndSpace($options[2])) > 0){
                $poll->setOptionThree($options[2]);
            }

            if(isset($options[3]) && strlen($this->stripHTMLAndSpace($options[3])) > 0){
                $poll->setOptionFour($options[3]);
            }

            $expire = new \DateTime();
            $expire->modify("+1 day");

            $poll->setExpireTimestamp($expire);

            $em->persist($poll);

            $post->setPoll($poll);
        }

        if(!is_null($magnetURL) && $this->isValidMagnet($magnetURL)){
            $magnet = new Magnet();
            $magnet->setPostId($post->getId());
            $magnet->setMagnet($magnetURL);

            $em->persist($magnet);

            $post->setMagnet($magnet);
        }

        $mentions = $parser->mention($body);

        $mentionedUsers = [];
        foreach($mentions as $item){
            $mention = new Mention();
            $mention->setPostId($post->getId());
            $mention->setUserId($item['user_id']);
            $mention->setCauserId($user->getId());
            $mention->setIndices($item['indices']);

            $em->persist($mention);

            if(!in_array($mention->getUserId(), $mentionedUsers)){
                $notifier->add($user, $mention->getUserId(), Notification::$TYPE_MENTION, $post->getId());
                $mentionedUsers[] = $mention->getUserId();
            }
        }

        $tagsService->addTags($tags, $post->getId(), $post->getAuthor()->getId());

        $user->setStorage($user->getStorage() + $size);

        $em->flush();

        try{
            $qb = $em->createquerybuilder();
            $qb->update('App:User\Addons\Invite', 'i')
                ->set('i.complete', $qb->expr()->literal(true))
                ->where('i.invited = :invited')
                ->setParameter('invited', $author->getId())
                ->getQuery()
                ->execute();
        }catch (\Exception $e) { }

        if($post INSTANCEOF Post){
            return $this->respond([
                'post' => $post->toArray()
            ]);
        }


        return $this->respondWithErrors([], "There was a problem creating your post.");
    }

    private function validateRows(&$rows)
    {
        foreach($rows as $i => $row) {
            if($i === 0 && $row !== 0){
                $rows = [];
                break;
            }

            // Check if in order (e.g. 0, 0, 1, 2, 3)
            if($i > 0 && isset($rows[$i - 1]) && !in_array(($rows[$i] - $rows[$i - 1]), [0, 1])){
                $rows = [];
                break;
            }

            if(isset($rows[$i - 2]) && $rows[$i] === $rows[$i - 2]){
                $rows = [];
                break;
            }

            if(isset($rows[$i + 2]) && $rows[$i] === $rows[$i + 2]){
                $rows = [];
                break;
            }

            if($i > 10 || $row > 10){
                $rows = [];
                break;
            }
        }
    }

    private function createThumbnails(ResourceInterface $thumbResource, Attachment $attachment): void
    {
        $em = $this->getDoctrine()->getManager();

        $thumbResource->convertToJpg();
        $thumbResource->maxSize(0.00025);

        $thumbnail = new Thumbnail();
        $thumbnail->setAttachment($attachment);

        $thumbFile = $em->getRepository(File::class)->findOneBy([
            'hash' => $thumbResource->getHash(),
        ]);

        if(!($thumbFile instanceof File)){
            $thumbFile = new File();
            $thumbFile->setURL($thumbResource->upload());
            $thumbFile->setHash($thumbResource->getHash());
            $thumbFile->setHashName($thumbResource->getHashName());
            $thumbFile->setFileSize($thumbResource->getFileSize());
            $thumbFile->setExtension($thumbResource->getExtension());

            if($thumbResource INSTANCEOF Image){
                $thumbFile->setWidth($thumbResource->getWidth());
                $thumbFile->setHeight($thumbResource->getHeight());
            }

            $em->persist($thumbFile);
        }

        $thumbnail->setFile($thumbFile);
        $attachment->addThumbnail($thumbnail);

        $thumbnail = new Thumbnail();
        $thumbnail->setAttachment($attachment);

        $thumbResource->resize(720, 405);
        $thumbResource->maxSize(0.0001);

        $thumbFile = $em->getRepository(File::class)->findOneBy([
            'hash' => $thumbResource->getHash(),
        ]);

        if(!($thumbFile instanceof File)){
            $thumbFile = new File();
            $thumbFile->setURL($thumbResource->upload());
            $thumbFile->setHash($thumbResource->getHash());
            $thumbFile->setHashName($thumbResource->getHashName());
            $thumbFile->setFileSize($thumbResource->getFileSize());
            $thumbFile->setExtension($thumbResource->getExtension());

            if($thumbResource INSTANCEOF Image){
                $thumbFile->setWidth($thumbResource->getWidth());
                $thumbFile->setHeight($thumbResource->getHeight());
            }

            $em->persist($thumbFile);
        }

        $thumbnail->setFile($thumbFile);
        $attachment->addThumbnail($thumbnail);
    }


    private function stripHTMLAndSpace($value)
    {
        return strlen(preg_replace('/\s+/', '', strip_tags($value)));
    }

    private function isValidMagnet($magnet)
    {
        if(preg_match('/magnet:\?xt=urn:[a-z0-9]/', $magnet)){
            return true;
        }

        return false;
    }
}
