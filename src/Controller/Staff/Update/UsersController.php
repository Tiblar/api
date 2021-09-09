<?php

namespace App\Controller\Staff\Update;

use App\Controller\ApiController;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends ApiController
{
    /**
     * @Route("/update/user/{userId}/ban", name="staff_api_update_user_ban", methods={"POST"})
     */
    public function ban(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();
        try{

            $user = $em->getRepository(User::class)->findOneBy([
                'id' => $userId,
            ]);

            if(!($user instanceof User)){
                return $this->respondWithErrors([
                    'user' => 'not found'
                ], "Not Found...", 404);
            }

            $qb = $em->createQueryBuilder();
            $qb->update('App:Post\Post', 'p')
                ->set('p.hidden', $qb->expr()->literal(true))
                ->where('p.author = :userId')
                ->setParameter('userId', $user->getId())
                ->getQuery()
                ->execute();

            $user->setBanned(true);

            $em->flush();

            $em->getConnection()->commit();
        }catch (\Exception $e) {
            $em->getConnection()->rollback();

            return $this->respondWithErrors([], $e->getMessage(), 500);
        }

        return $this->respond([]);
    }

    /**
     * @Route("/update/user/{userId}/ban", name="staff_api_update_user_ban", methods={"DELETE"})
     */
    public function unban(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();
        try{

            $user = $em->getRepository(User::class)->findOneBy([
                'id' => $userId,
            ]);

            if(!($user instanceof User)){
                return $this->respondWithErrors([
                    'user' => 'not found'
                ], "Not Found...", 404);
            }

            $qb = $em->createQueryBuilder();
            $qb->update('App:Post\Post', 'p')
                ->set('p.hidden', $qb->expr()->literal(false))
                ->where('p.author = :userId')
                ->setParameter('userId', $user->getId())
                ->getQuery()
                ->execute();

            $user->setBanned(false);

            $em->flush();

            $em->getConnection()->commit();
        }catch (\Exception $e) {
            $em->getConnection()->rollback();

            return $this->respondWithErrors([], $e->getMessage(), 500);
        }

        return $this->respond([]);
    }
}