<?php
namespace App\Service\User;

use App\Entity\User\ActionLog;
use App\Entity\User\User;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class UserLogger
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var RequestStack $requestStack
     */
    private $requestStack;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function add(?User $user, string $action, ?array $metadata = null)
    {
        $log = new ActionLog();
        $log->setAuthor($user);
        $log->setAction($action);
        $log->setMetadata($metadata);
        $log->setIpAddress($this->requestStack->getMasterRequest()->getClientIp());

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param array $actions
     * @param \DateTime $since
     * @param array $options
     * @return array
     */
    public function count(array $actions, \DateTime $since, array $options = []): array
    {
        $userId = isset($options['user_id']) && is_string($options['user_id']) ? $options['user_id'] : null;
        $ip = isset($options['ip']) && is_bool($options['ip']) ? $options['ip'] : null;

        $count = [];
        foreach($actions as $action){
            if(!isset($count[$action])){
                $count[$action] = 0;
            }
        }

        if(is_null($userId) && !$ip){
            return $count;
        }

        $qb = $this->em->createQueryBuilder()
            ->select('l')
            ->from('App:User\ActionLog', 'l')
            ->where('l.action IN (:actions)')
            ->andWhere('l.timestamp > :date');

        if(!is_null($userId)){
            $qb->andWhere('l.author = :author')
                ->setParameter('author', $userId);
        }

        if(!is_null($ip) && $ip === true){
            $qb->andWhere('l.ipAddress = :ip')
                ->setParameter('ip', $this->requestStack->getMasterRequest()->getClientIp());
        }

        $logs = $qb->setParameter('actions', $actions)
            ->setParameter('date', $since)
            ->getQuery()
            ->getArrayResult();

        foreach($logs as $log){
            if(isset($count[$log['action']])){
                $count[$log['action']]++;
            }else{
                $count[$log['action']] = 1;
            }
        }

        return $count;
    }
}
