<?php
namespace App\Command;

use App\Service\S3\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveUnusedFilesCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:remove-unused-files';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Client
     */
    private $client;

    public function __construct(EntityManagerInterface $em, Client $client, string $name = null)
    {
        $this->em = $em;
        $this->client = $client;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Remove files with no attachments.')
            ->setHelp('This command removes files with no attachments.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $expr = $this->em->getExpressionBuilder();
        $files = $this->em->createQueryBuilder()
            ->select('f')
            ->from('App:Media\File', 'f')
            ->where($expr->notIn(
                'f.id',
                $this->em->createQueryBuilder()
                    ->select('ff.id')
                    ->from('App:Media\Attachment', 'a')
                    ->join('a.file', 'ff')
                    ->getDQL()
            ))
            ->andWhere($expr->notIn(
                'f.id',
                $this->em->createQueryBuilder()
                    ->select('aa.id')
                    ->from('App:User\UserInfo', 'u')
                    ->join('u.avatar', 'aa')
                    ->getDQL()
            ))
            ->andWhere($expr->notIn(
                'f.id',
                $this->em->createQueryBuilder()
                    ->select('tt.id')
                    ->from('App:Media\Thumbnail', 't')
                    ->join('t.file', 'tt')
                    ->getDQL()
            ))
            ->andWhere('f.fileSize > :minSize')
            ->setParameter('minSize', 0.00025)
            ->orderBy('CHAR_LENGTH(f.id)', 'ASC')
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $deleteIds = [];
        foreach($files as $file){
            try{
                $this->client->remove($file['hashName']);

                $deleteIds[] = $file['id'];
            }catch (\Exception $e) {
                $io->writeln("Erorr with " . $file['id']);
                $io->writeln($e->getMessage());
            }

            sleep(.15);

            $io->writeLn("Removing file.");

            if(count($deleteIds) >= 10){
                $io->writeLn("Persisting.");

                $this->em->createQueryBuilder()
                    ->delete('App:Media\File', 'f')
                    ->where('f.id IN (:ids)')
                    ->setParameter('ids', $deleteIds)
                    ->getQuery()->getResult();
                $deleteIds = [];
            }
        }

        $this->em->createQueryBuilder()
            ->delete('App:Media\File', 'f')
            ->where('f.id IN (:ids)')
            ->setParameter('ids', $deleteIds)
            ->getQuery()->getResult();
        $io->writeLn("Persisting.");

        return Command::SUCCESS;
    }
}