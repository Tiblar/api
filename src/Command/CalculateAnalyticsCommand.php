<?php
namespace App\Command;

use App\Entity\Analytics\PostAnalytics;
use App\Entity\Analytics\UserAnalytics;
use App\Entity\Analytics\ViewLog;
use App\Service\S3\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CalculateAnalyticsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:calculate-analytics';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Client
     */
    private $blaze;

    public function __construct(EntityManagerInterface $em, Client $blaze, string $name = null)
    {
        $this->em = $em;
        $this->blaze = $blaze;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Calculate analytics.')
            ->setHelp('This command calculates analytics.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->em->beginTransaction();
        try{
            $timestamp = new \DateTime("-2 days");

            $postData = $this->em->createQueryBuilder()
                ->addSelect('COUNT(l) as views')
                ->addSelect('l.source as source')
                ->addSelect('l.resourceId as postId')
                ->addSelect('l.userId as userId')
                ->addSelect('FROM_UNIXTIME((FLOOR(UNIX_TIMESTAMP(l.timestamp)/(60*60*24))*(60*60*24))) AS timestamp')
                ->from('App:Analytics\ViewLog', 'l')
                ->where('l.type = :type')
                ->andWhere('l.timestamp < :timestamp')
                ->setParameter('type', ViewLog::$VIEW_TYPE_POST)
                ->setParameter('timestamp', $timestamp)
                ->groupBy('timestamp', 'source', 'postId', 'userId')
                ->getQuery()
                ->getArrayResult();

            foreach($postData as $datum){
                $post = new PostAnalytics();
                $post->setUserId($datum['userId']);
                $post->setSource($datum['source']);
                $post->setViews($datum['views']);
                $post->setPostId($datum['postId']);
                $postTimestamp = new \DateTime($datum['timestamp']);
                $postTimestamp->setTime(0, 0, 0);

                $post->setTimestamp($postTimestamp);
                $this->em->persist($post);
            }

            $this->em->flush();

            $userData = $this->em->createQueryBuilder()
                ->addSelect('COUNT(l) as views')
                ->addSelect('l.userId as userId')
                ->addSelect('FROM_UNIXTIME((FLOOR(UNIX_TIMESTAMP(l.timestamp)/(60*60*24))*(60*60*24))) AS timestamp')
                ->from('App:Analytics\ViewLog', 'l')
                ->where('l.type = :type')
                ->andWhere('l.timestamp < :timestamp')
                ->setParameter('type', ViewLog::$VIEW_TYPE_USER)
                ->setParameter('timestamp', $timestamp)
                ->groupBy('timestamp', 'userId')
                ->getQuery()
                ->getArrayResult();

            foreach($userData as $datum){
                $user = new UserAnalytics();
                $user->setUserId($datum['userId']);
                $user->setViews($datum['views']);
                $userTimestamp = new \DateTime($datum['timestamp']);
                $userTimestamp->setTime(0, 0, 0);

                $user->setTimestamp($userTimestamp);
                $this->em->persist($user);
            }

            $this->em->flush();

            $this->em->createQueryBuilder()
                ->delete('App:Analytics\ViewLog', 'l')
                ->andWhere('l.timestamp < :timestamp')
                ->setParameter('timestamp', $timestamp)
                ->getQuery()->getResult();

            $this->em->getConnection()->commit();

            $io->success("Calculated.");

        }catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            $io->error($e->getMessage());
        }

        return Command::SUCCESS;
    }
}