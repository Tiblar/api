<?php
declare(strict_types=1);

namespace App\Entity\Application;

use App\Structure\Application\SanitizedApplication;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class AppRepository extends EntityRepository
{
    /**
     * @param string $clientId
     * @return array|null
     */
    public function findApplication(string $clientId): ?array
    {
        $app = $this->_em->createQueryBuilder()
            ->select('a, r')
            ->from('App:Application\Application', 'a')
            ->leftJoin('a.redirectURLs', 'r')
            ->where('a.id = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getArrayResult();

        if(count($app) === 0){
            return null;
        }

        if($app[0]['id'] === $clientId){
            $sanitized = new SanitizedApplication($app[0]);
            return $sanitized->toArray();
        }

        return null;
    }
}
