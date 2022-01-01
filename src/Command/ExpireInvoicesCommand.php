<?php
namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Billing\Invoice;

class ExpireInvoicesCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:expire-invoices';

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
        $this->setDescription('Expire old invoices.')
            ->setHelp('This command expires old invoices.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->update('App:Billing\Invoice', 'i')
            ->where('i.paymentStatus = :pending')
            ->andWhere('i.expireTimestamp IS NOT NULL')
            ->andWhere('i.expireTimestamp < :timestamp')
            ->set('i.paymentStatus', ':expired')
            ->setParameter('pending', Invoice::$INVOICE_STATUS_PENDING)
            ->setParameter('timestamp', new \DateTime())
            ->setParameter('expired', Invoice::$INVOICE_STATUS_EXPIRED)
            ->getQuery()
            ->execute();

        return Command::SUCCESS;
    }
}

