<?php
namespace App\Service\Connection\Discord;

use App\Entity\Media\File;
use App\Entity\User\Addons\Connection;
use App\Service\Content\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;

class DiscordConnect{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var Resource
     */
    private $resource;

    private $url;
    private $client;
    private $secret;
    private $return;

    public function __construct
    (
        EntityManagerInterface $em, Security $security, Resource $resource,
        $url, $client, $secret, $return
    ){
        $this->em = $em;
        $this->security = $security;
        $this->resource = $resource;

        $this->url = $url;
        $this->client = $client;
        $this->secret = $secret;
        $this->return = $return;
    }

    public function getURL()
    {
        return $this->url;
    }

    public function validate($code)
    {
        $post = \http_build_query([
            'client_id' => $this->client,
            'client_secret' => $this->secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->return,
            'scope' => 'identify'
        ]);

        $ch = \curl_init();

        \curl_setopt($ch, CURLOPT_URL,"https://discordapp.com/api/oauth2/token");
        \curl_setopt($ch, CURLOPT_POST, 1);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        \curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = \json_decode(\curl_exec($ch));

        \curl_close($ch);

        if(!isset($data->access_token)){
            return false;
        }

        $access = $data->access_token;
        $refresh = $data->refresh_token;

        $ch = \curl_init();

        \curl_setopt($ch, CURLOPT_URL,"https://discordapp.com/api/users/@me");
        \curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access]);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = \json_decode(\curl_exec($ch));

        \curl_close($ch);

        if(isset($data->id)){
            $connectionRepo = $this->em->getRepository(Connection::class);
            $connection = $connectionRepo->findOneBy([
                'service' => Connection::$SERVICE_DISCORD,
                'userId' => $this->security->getToken()->getUser()->getId(),
            ]);

            if(!$connection INSTANCEOF Connection){
                $avatar = "https://cdn.discordapp.com/avatars/" . $data->id . "/" . $data->avatar . ".png";

                $temp = tmpfile();
                fwrite($temp, file_get_contents($avatar));

                $uploadedFile = new UploadedFile(
                    stream_get_meta_data($temp)['uri'],
                    "avatar.png",
                    "image/png"
                );

                $resource = $this->resource->getFile($uploadedFile);

                $file = $this->em->getRepository(File::class)->findOneBy([
                    'hash' => $resource->getHash(),
                ]);

                if(!$file INSTANCEOF File){
                    $file = new File();

                    $file->setFileSize($resource->getFileSize());
                    $file->setHash($resource->getHash());
                    $file->setHashName($resource->getHash() . ".png");
                    $file->setHeight($resource->getHeight());
                    $file->setWidth($resource->getWidth());
                    $file->setURL($resource->upload());

                    $this->em->persist($file);
                }

                $connection = new Connection();
                $connection->setService('discord');
                $connection->setUserId($this->security->getToken()->getUser()->getUsername());
                $connection->setAccount($data->username . "#" . $data->discriminator);
                $connection->setLink($file->getURL());

                $this->em->persist($connection);
                $this->em->flush();
                $this->em->clear();
            }

            return true;
        }

        return false;
    }
}