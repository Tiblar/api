<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use App\Entity\Media\File;
use App\Entity\User\Addons\Invite;
use App\Entity\User\Addons\Privacy;
use App\Entity\User\JwtRefreshToken;
use App\Service\Generator\Securimage;
use App\Entity\User\User;
use App\Entity\User\UserInfo;
use App\Service\Content\Resource;
use App\Service\Matrix\MatrixInterface;
use App\Service\User\Validator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends ApiController
{
    /**
     * @Route("/auth/register", name="user_registration", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder,
                             Resource $resource, JWTTokenManagerInterface $JWTManager,
                             Validator $validator, Securimage $securimage, MatrixInterface $matrix, LoggerInterface $logger
    ){

        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $inviteUser = $request->request->get('invite');
        $securityId = $request->request->get('security_id');
        $securityCode = $request->request->get('security_code');

        $em = $this->getDoctrine()->getManager();

        if($securimage->isValid($securityId, $securityCode) == false){
            return $this->respondWithErrors([
                'captcha' => 'Invalid security code.'
            ], 'Security code error.');
        }

        $usernameValidator = $validator->username($username);
        if($usernameValidator !== true){
            return $this->respondWithErrors([
                'username' => $usernameValidator
            ], 'Authentication error.');
        }

        if(!is_string($email) || strlen($email) === 0 || empty($email)){
            $email = null;
        }
        
        $emailValidator = $validator->email($email);
        if($emailValidator !== true && !is_null($email)){
            return $this->respondWithErrors([
                'email' => $emailValidator
            ], 'Authentication error.');
        }

        $avatarFile = new UploadedFile(
            $this->getParameter('kernel.project_dir') . '/src/Assets/Avatar/' . rand(1, 5) . '.png',
            'avatar.png',
            'image/png',
            null,
            false
        );

        $userId = null;

        try{
            $resource = $resource->getAvatar($avatarFile);

            $avatar = $em->getRepository(File::class)->findOneBy([
                'hash' => $resource->getHash()
            ]);

            // Create avatar
            if(!$avatar INSTANCEOF File){
                $avatar = new File();

                $avatar->setFileSize($resource->getFileSize());
                $avatar->setHash($resource->getHash());
                $avatar->setHashName($resource->getHash() . ".png");
                $avatar->setExtension("png");
                $avatar->setHeight($resource->getHeight());
                $avatar->setWidth($resource->getWidth());
                $resource->upload();

                $em->persist($avatar);
            }

            // Create user
            $user = new User([
                'username' => $username,
            ]);

            $info = $user->getInfo();

            $user->setEmail($email);
            $user->setPassword($passwordEncoder->encodePassword($user, $password));

            $em->persist($user);

            $userId = $user->getId();

            // Create privacy entity
            $privacy = new Privacy();
            $privacy->setUserId($user->getId());
            $em->persist($privacy);

            $user->setPrivacy($privacy);

            // Set info settings
            $info->setId($user->getId());
            $info->setAvatar($avatar);
            $info->setUsername($username);
            $em->persist($info);

            $expireTimestamp = new \DateTime();
            $expireTimestamp->modify("+30 days");

            // Create JWT Token
            $jwtRefreshToken = new JwtRefreshToken();
            $jwtRefreshToken->setUserId($user->getId());
            $jwtRefreshToken->setExpireTimestamp($expireTimestamp);
            $jwtRefreshToken->setRefreshToken(\bin2hex(\openssl_random_pseudo_bytes(32)));
            $em->persist($jwtRefreshToken);

            // See if user has been invited
            if(is_string($inviteUser)){
                $inviteUser = $em->getRepository(UserInfo::class)->findOneBy([
                    'username' => $inviteUser,
                ]);

                if($inviteUser INSTANCEOF UserInfo){
                    $invite = new Invite();
                    $invite->setInvited($user->getId());
                    $invite->setInviter($inviteUser->getId());
                    $invite->setComplete(false);

                    $em->persist($invite);
                }
            }


            // Flush to database
            $em->flush();
            $em->clear();

            $token = $JWTManager->create($user);

            $cookie = new Cookie('AUTH_TOKEN', // Cookie name, should be the same as in config/packages/lexik_jwt_authentication.yaml.
                $token, // cookie value
                time() + $this->getParameter('jwt_ttl'), // expiration
                '/', // path
                null, // domain, null means that Symfony will generate it on its own.
                $this->getParameter('cookie_secure'), // secure
                true, // httpOnly
                false, // raw
                'strict' // same-site parameter, can be 'lax' or 'strict'.);
            );

            return $this->respondWithCookies([
                'token' => $token,
                'refresh_token' => $jwtRefreshToken->getRefreshToken(),
                'refresh_expire' => time() + 2592000,
            ], [$cookie]);
        }catch(\Exception $e) {
            $logger->debug($e->getMessage());
            $logger->debug("DELETING " . $userId);

            $em->createQueryBuilder()
                ->delete('App:User\User', 'u')
                ->where('u.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getResult();

            $em->createQueryBuilder()
                ->delete('App:User\UserInfo', 'u')
                ->where('u.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getResult();

            $em->createQueryBuilder()
                ->delete('App:User\Addons\Privacy', 'p')
                ->where('p.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getResult();
        }

        return $this->respondWithErrors([], 'Authentication error.');
    }
}