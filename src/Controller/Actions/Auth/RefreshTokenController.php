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
use App\Service\User\Validator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

class RefreshTokenController extends ApiController
{
    /**
     * @Route("/auth/token/refresh", name="api_jwt_refresh_token", methods={"POST"})
     */
    public function refreshToken(Request $request, JWTTokenManagerInterface $JWTManager)
    {
        $refreshToken = $request->request->get('refresh_token');

        $em = $this->getDoctrine()->getManager();

        $jwtRefreshToken = $em->getRepository(JwtRefreshToken::class)->findOneBy([
            'refreshToken' => $refreshToken,
        ]);

        if(!$jwtRefreshToken INSTANCEOF JwtRefreshToken){
            return $this->respondWithErrors([
                'token' => 'Invalid refresh token.'
            ], 'Authentication error.', 401);
        }

        $now = new \DateTime();
        if($jwtRefreshToken->getExpireTimestamp()->diff($now)->days > 30){
            return $this->respondWithErrors([
                'token' => 'Expired refresh token.'
            ], 'Authentication error.', 401);
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $jwtRefreshToken->getUserId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid refresh token.'
            ], 'Authentication error.', 401);
        }

        if($user->isBanned()){
            return $this->respondWithErrors([
                'token' => 'You have been banned.'
            ], 'Authentication error.', 401);
        }

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
        ], [$cookie], 200);
    }
}