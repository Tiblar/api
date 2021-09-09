<?php

namespace App\Controller\Staff;

use App\Controller\ApiController;
use App\Entity\Post\Post;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BanController extends ApiController
{
    /**
     * @Route("/ban/{userId}", name="staff_api_ban", methods={"POST"})
     */
    public function ban(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction(); // suspend auto-commit
        try {

            $user = $em->getRepository(User::class)->findOneBy([
                'id' => $userId,
            ]);

            if(!$user INSTANCEOF User){
                return $this->respondWithErrors([], null, 404);
            }

            $user->setBanned(true);

            $qb = $em->createQueryBuilder();
            $qb->update('App:Post\Post', 'p')
                ->where('p.author = :userId')
                ->set('p.hidden', $qb->expr()->literal(true))
                ->setParameter('userId', $user->getId())
                ->getQuery()
                ->execute();

            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }

        return $this->respond([]);
    }

    /**
     * @Route("/ban/{userId}", name="staff_api_unban", methods={"DELETE"})
     */
    public function unban(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction(); // suspend auto-commit
        try {

            $user = $em->getRepository(User::class)->findOneBy([
                'id' => $userId,
            ]);

            if(!$user INSTANCEOF User){
                return $this->respondWithErrors([], null, 404);
            }

            $user->setBanned(false);

            $qb = $em->createQueryBuilder();
            $qb->update('App:Post\Post', 'p')
                ->where('p.author = :userId')
                ->set('p.hidden', $qb->expr()->literal(false))
                ->setParameter('userId', $user->getId())
                ->getQuery()
                ->execute();

            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }

        return $this->respond([]);
    }
}