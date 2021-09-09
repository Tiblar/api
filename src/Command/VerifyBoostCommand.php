<?php
namespace App\Command;

use App\Entity\Billing\Invoice;
use App\Entity\Billing\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VerifyBoostCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:verify-formerly-chucks-boost';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params, string $name = null)
    {
        $this->em = $em;
        $this->params = $params;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Verify Formerly Chuck\'s boost subscriptions.')
            ->setHelp('This command verifies Formerly Chuck\'s boost subscriptions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $productId = $this->params->get('boost_product_id');
        $legacyUserIds = $this->params->get('legacy_boost_users');

        $product = $this->em->getRepository(Product::class)->findOneBy([
            'id' => $productId,
        ]);

        if(!$product instanceof Product){
            $io->error("Product does not exist.");
            return Command::FAILURE;
        }

        $timestamp = new \DateTime();
        $timestamp = $timestamp->modify("-1 week");

        $this->em->beginTransaction();
        try{
            $qb = $this->em->createQueryBuilder();

            $qb = $qb->select('o.buyerId, o.id')
                ->from('App:Billing\Order', 'o')
                ->where('o.product = :productId')
                ->andWhere('o.expired = false')
                ->andWhere('o.expireTimestamp < :timestamp');

            $qb->having(
                $qb->expr()->eq(
                    "(" .
                    $this->em
                        ->createQueryBuilder()
                        ->select('COUNT(oc.id)')
                        ->from('App:Billing\Order', 'oc')
                        ->where('oc.product = :productId')
                        ->andWhere('oc.buyerId = o.buyerId')
                        ->andWhere('oc.expired = false')
                        ->andWhere('oc.expireTimestamp > :timestamp')
                        ->getDQL()
                    . ")"
                    ,
                    0
                )
            );

            $expiredIds = $qb->setParameter('productId', $productId)
                ->setParameter('timestamp', $timestamp)
                ->getQuery()
                ->getArrayResult();
            $expiredUserIds = array_column($expiredIds, "buyerId");
            $expiredOrderIds = array_column($expiredIds, "id");

            if(!empty($expiredIds)){
                $this->em->createQueryBuilder()
                    ->update('App:Billing\Order', 'o')
                    ->set('o.expired', $qb->expr()->literal(true))
                    ->where('o.id IN (:expiredIds)')
                    ->setParameter('expiredIds', $expiredOrderIds)
                    ->getQuery()
                    ->execute();

                $this->em->createQueryBuilder()
                    ->update('App:User\User', 'u')
                    ->set('u.boosted', $qb->expr()->literal(false))
                    ->set('u.storageLimit', $qb->expr()->literal(0.5))
                    ->where('u.id IN (:expiredIds)')
                    ->andWhere("u.id NOT IN (:legacyUserIds)")
                    ->setParameter('expiredIds', $expiredUserIds)
                    ->setParameter('legacyUserIds', $legacyUserIds)
                    ->getQuery()
                    ->execute();
            }

            $this->em->getConnection()->commit();
        }catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $this->em->beginTransaction();
        try{
            if(!empty($expiredIds)){
                $qb = $this->em->createQueryBuilder();

                $qb->update('App:User\User', 'u')
                    ->set('u.storageLimit', $qb->expr()->literal(
                        "(" .
                        $this->em
                            ->createQueryBuilder()
                            ->select('MAX(a.quantity)')
                            ->from('App:Billing\BillingAttribute', 'a')
                            ->leftJoin('a.order', 'o')
                            ->where('o.expired = false')
                            ->andWhere('o.product = :productId')
                            ->andWhere('a.buyerId = u.id')
                            ->getDQL()
                        . ")"
                    ))
                    ->where('u.id IN (:expiredIds)');

                $qb->having(
                    $qb->expr()->gt(
                        "(" .
                        $this->em
                            ->createQueryBuilder()
                            ->select('COUNT(a.buyerId)', 'MAX(a.quantity) AS HIDDEN quantity')
                            ->from('App:Billing\BillingAttribute', 'a')
                            ->leftJoin('a.order', 'o')
                            ->where('o.expired = false')
                            ->andWhere('o.product = :productId')
                            ->andWhere('a.buyerId = u.id')
                            ->getDQL()
                        . ")"
                        ,
                        0
                    )
                );

                $qb->setParameter('productId', $productId)
                    ->setParameter('expiredIds', $expiredIds)
                    ->getQuery()
                    ->execute();

                $this->em->getConnection()->commit();
            }
        }catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            $io->error($e->getMessage());
        }

        return Command::SUCCESS;
    }
}