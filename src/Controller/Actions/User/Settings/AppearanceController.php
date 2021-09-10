<?php

namespace App\Controller\Actions\User\Settings;

use App\Controller\ApiController;
use App\Entity\Media\File;
use App\Service\Color;
use App\Service\Matrix\MatrixInterface;
use App\Service\User\GetMe;
use App\Structure\User\SanitizedUser;
use App\Entity\User\User;
use App\Service\Content\Resource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppearanceController extends ApiController
{
    /**
     * @Route("/users/@me/settings/avatar", name="appearance_avatar", methods={"POST"})
     */
    public function avatar(Request $request, MatrixInterface $matrix, Resource $resourceFactory, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $uploadedFile = $request->files->get('file');

        if(is_null($uploadedFile)){
            return $this->respondWithErrors([
                'file' => 'An image file is required.'
            ], null, 400);
        }

        $resource = $resourceFactory->getAvatar($uploadedFile);

        $fileRepository = $em->getRepository(File::class);
        $avatar = $fileRepository->findOneBy([
            'hash' => $resource->getHash(),
        ]);

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

        $user->getInfo()->setAvatar($avatar);

        if(!$matrix->updateUser($user, [ 'avatar_contents' => $resource->getContents() ])){
            return $this->respondWithErrors([
                'matrix' => 'Error updating Matrix account.'
            ]);
        }

        $em->flush();

        return $this->respond($me->toArray($user));
    }

    /**
     * @Route("/users/@me/settings/biography", name="appearance_biography", methods={"PATCH"})
     */
    public function biography(Request $request, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $biography = $request->request->get('biography');
        $biography = substr($biography, 0, 600);
        $biography = ctype_space($biography) ? null : $biography;

        $user->getInfo()->setBiography($biography);

        $em->flush();

        return $this->respond($me->toArray($user));
    }

    /**
     * @Route("/users/@me/settings/location", name="appearance_location", methods={"PATCH"})
     */
    public function location(Request $request, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $location = $request->request->get('location');
        $location = substr($location, 0, 30);
        $location = ctype_space($location) ? null : $location;

        $user->getInfo()->setLocation($location);

        $em->flush();

        return $this->respond($me->toArray($user));
    }

    /**
     * @Route("/users/@me/settings/nsfw", name="appearance_nsfw", methods={"PATCH"})
     */
    public function nsfw(Request $request, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $nsfw = $request->request->get('nsfw');
        if(!is_null($nsfw) && is_bool($nsfw)){
            $user->getInfo()->setNsfw($nsfw);

            $em->flush();
        }

        $sanitizedUser = new SanitizedUser($user);
        $array = $sanitizedUser->toArray();
        $array['nsfw_filter'] = $user->getNsfwFilter();
        $array['theme'] = $user->getTheme();

        return $this->respond($me->toArray($user));
    }

    /**
     * @Route("/users/@me/settings/nsfw-filter", name="appearance_nsfw_filter", methods={"PATCH"})
     */
    public function nsfwFilter(Request $request, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $nsfwFilter = $request->request->get('nsfw_filter');
        if(!is_null($nsfwFilter) && is_bool($nsfwFilter)){
            $user->setNsfwFilter($nsfwFilter);

            $em->flush();
        }

        return $this->respond($me->toArray($user));
    }

    /**
     * @Route("/users/@me/settings/username-color", name="appearance_username_color", methods={"PATCH"})
     */
    public function usernameColor(Request $request, Color $color, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $hex = $request->request->get('hex_color');

        if(is_null($hex)){
            $user->getInfo()->setUsernameColor(null);
        }else{
            if(!$user->isBoosted()){
                return $this->respondWithErrors([
                    'user' => 'You must be a boost user.'
                ], null, 403);
            }

            if($color->validateUsername($hex)){
                $user->getInfo()->setUsernameColor($hex);
            }else{
                return $this->respondWithErrors([
                    'hex_color' => 'Invalid color.'
                ], null, 400);
            }
        }

        $em->flush();

        return $this->respond($me->toArray($user));
    }

    /**
     * @Route("/users/@me/settings/profile-theme", name="appearance_profile_theme", methods={"PATCH"})
     */
    public function profileTheme(Request $request, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $profileTheme = $request->request->get('profile_theme');
        if(in_array($profileTheme, [null, 'clover', 'cozy', 'outrun', 'cyberpunk', 'newspaper', 'skeleton'])){
            $user->getInfo()->setProfileTheme($profileTheme);

            $em->flush();
        }

        return $this->respond($me->toArray($user));
    }

    /**
     * @Route("/users/@me/settings/theme", name="appearance_theme", methods={"PATCH"})
     */
    public function theme(Request $request, GetMe $me)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $theme = $request->request->get('theme');
        if(in_array($theme, ['light', 'dark'])){
            $user->setTheme($theme);

            $em->flush();
        }

        return $this->respond($me->toArray($user));
    }
}