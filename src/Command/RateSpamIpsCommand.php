<?php
namespace App\Command;

use App\Entity\SpamFilter\IpList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RateSpamIpsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:rate-spam-ips';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        $this->em = $em;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Update rating of IP addresses.')
            ->setHelp('This command updates the ratings of IP addresses.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em->createQueryBuilder()
            ->update(IpList::class, 'i')
            ->set('i.rating', "(i.rating - 0.05)")
            ->getQuery()
            ->execute();

        $this->em->createQueryBuilder()
            ->update(IpList::class, 'i')
            ->set('i.rating', "0")
            ->where("i.rating < 0")
            ->getQuery()
            ->execute();

        return Command::SUCCESS;
    }
}