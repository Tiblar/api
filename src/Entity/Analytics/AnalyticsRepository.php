<?php
namespace App\Entity\Analytics;

use Doctrine\ORM\EntityRepository;

class AnalyticsRepository extends EntityRepository
{
    public function addViews(array $resourceIds, array $userIds, string $source, string $ip, string $type): void
    {
        $timestamp =  new \DateTime("now");

        if(empty($resourceIds)){
            return;
        }

        $existingIds = $this->_em->createQueryBuilder()
            ->select('l.resourceId')
            ->from('App:Analytics\ViewLog', 'l')
            ->where('l.resourceId in (:resourceIds)')
            ->andWhere('l.ipAddress = :ip')
            ->andWhere('l.expireTimestamp > :timestamp')
            ->setParameter('resourceIds', $resourceIds)
            ->setParameter('ip', $ip)
            ->setParameter('timestamp', $timestamp)
            ->getQuery()
            ->getArrayResult();
        $existingIds = array_column($existingIds, "resourceId");

        $newIds = array_diff($resourceIds, $existingIds);

        foreach($newIds as $id){
            if(!isset($userIds[$id])){
                throw new \Exception("User ID not found to add view.");
            }

            $minutes = rand(25, 50);
            $timestamp = new \DateTime("+$minutes minutes");

            $viewLog = new ViewLog();
            $viewLog->setResourceId($id);
            $viewLog->setUserId($userIds[$id]);
            $viewLog->setSource($source);
            $viewLog->setIPAddress($ip);
            $viewLog->setType($type);
            $viewLog->setExpireTimestamp($timestamp);

            $this->_em->persist($viewLog);
        }

        $this->_em->flush();
    }
}
