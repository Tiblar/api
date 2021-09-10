<?php
namespace App\Command;

use App\Service\S3\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CalculateStorageCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:calculate-storage';

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
        $this->setDescription('Removes old uploaded files.')
            ->setHelp('This command cleans up old uploaded files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $timestamp = new \DateTime();
        $timestamp = $timestamp->modify("-1 day");

        $bbIds = $this->em->createQueryBuilder()
            ->select('f.s3FileId as bbId')
            ->from('App:Media\FileInit', 'f')
            ->where('f.timestamp < :timestamp')
            ->setParameter('timestamp', $timestamp)
            ->getQuery()
            ->execute();

        $bbIds = array_column($bbIds, 'bbId');

        $deleteIds = [];
        foreach($bbIds as $id){
            try{
                $this->blaze->deleteLargeFile($id);
            }catch (\Exception $e) {
                $io->writeln($e->getMessage());
            }

            $deleteIds[] = $id;
            sleep(0.15);

            $io->writeLn("Removing large file.");

            if(count($deleteIds) > 10){
                $io->writeLn("Persisting.");

                $this->em->createQueryBuilder()
                    ->delete('App:Media\FileInit', 'f')
                    ->where('f.s3FileId IN (:ids)')
                    ->setParameter('ids', $deleteIds)
                    ->getQuery()->getResult();

                $deleteIds = [];
            }
        }

        $this->em->createQueryBuilder()
            ->delete('App:Media\FileInit', 'f')
            ->where('f.timestamp < :timestamp')
            ->setParameter('timestamp', $timestamp)
            ->getQuery()->getResult();

        $qb = $this->em->createQueryBuilder();

        $select = $qb->select('COALESCE(SUM(f.fileSize), 0)')
            ->from('App:Media\Attachment', 'a')
            ->leftJoin('a.post', 'p')
            ->leftJoin('a.file', 'f')
            ->where('p.author = u.id');


        $this->em->createQueryBuilder()
            ->update('App:User\User', 'u')
            ->set('u.storage', "(" . $select->getQuery()->getDQL() . ")")
            ->getQuery()
            ->execute();

        $io->success("Calculated.");

        return Command::SUCCESS;
    }
}