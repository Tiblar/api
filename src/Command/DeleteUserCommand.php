<?php
namespace App\Command;

use App\Service\S3\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteUserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:delete-user';

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
        $this->setDescription('Delete user.')
            ->setHelp('This command deletes a user.');
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'User ID to delete.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('id');

        $this->em->beginTransaction();
        try{

            $this->em->createQueryBuilder()
                ->delete('App:Analytics\PostAnalytics', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Analytics\UserAnalytics', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Analytics\ViewLog', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $aIds = $this->em->createQueryBuilder()
                ->select('d.id as id')
                ->from('App:Application\OAuth\AccessToken', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->execute();
            $aIds = array_column($aIds, 'id');

            $this->em->createQueryBuilder()
                ->delete('App:Application\OAuth\RefreshToken', 'd')
                ->where('d.accessToken IN (:ids)')
                ->setParameter('ids', $aIds)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Application\OAuth\AccessToken', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Application\OAuth\Code', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Application\Application', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\BillingAttribute', 'd')
                ->where('d.buyerId = :userId OR d.sellerId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\CryptoPaymentMethod', 'd')
                ->where('d.userId= :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\Invoice', 'd')
                ->where('d.buyerId = :userId OR d.sellerId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\Order', 'd')
                ->where('d.buyerId = :userId OR d.sellerId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\PaymentMethod', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\Product', 'd')
                ->where('d.user = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\ProductAttribute', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\StripeCustomer', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Billing\StripePaymentMethod', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Lists\ListItem', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Lists\PostList', 'd')
                ->where('d.author = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $bbIds = $this->em->createQueryBuilder()
                ->select('d.s3FileId as bbId')
                ->from('App:Media\FileInit', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->execute();

            $bbIds = array_column($bbIds, 'bbId');

            $deleteIds = [];
            foreach($bbIds as $id){
                try{
                    $this->blaze->deleteLargeFile($id);
                    $deleteIds[] = $id;
                }catch (\Exception $e) {
                    $io->writeln($e->getMessage());
                }

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
                ->delete('App:Media\FilePart', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $postIds = $this->em->createQueryBuilder()
                ->select('p.id')
                ->from('App:Post\Post', 'p')
                ->where('r.author = :userId')
                ->leftJoin('p.reblog', 'r')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getArrayResult();
            $postIds = array_column($postIds, "id");

            $this->deletePosts($postIds, $userId);

            $postIds = $this->em->createQueryBuilder()
                ->select('p.id')
                ->from('App:Post\Post', 'p')
                ->where('p.author = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getArrayResult();
            $postIds = array_column($postIds, "id");

            $this->deletePosts($postIds, $userId);

            $this->em->createQueryBuilder()
                ->update('App:Post\Reply', 'd')
                ->where('d.author = :userId')
                ->set('d.author', ':null')
                ->set('d.body', ':body')
                ->setParameter('userId', $userId)
                ->setParameter('null', null)
                ->setParameter('body', '[deleted]')
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Report\PostReport', 'd')
                ->where('d.postId IN (:postIds)')
                ->orWhere('d.userId = :userId')
                ->setParameter('postIds', $postIds)
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Block', 'd')
                ->where('d.blockerId = :userId')
                ->orWhere('d.blockedId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\ConfirmEmail', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Connection', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Disable2FAEmail', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Follow', 'd')
                ->where('d.followerId = :userId')
                ->orWhere('d.followedId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\FollowRequest', 'd')
                ->where('d.requestedId = :userId')
                ->orWhere('d.requesterId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Invite', 'd')
                ->where('d.inviter = :userId')
                ->orWhere('d.invited = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Notification', 'd')
                ->where('d.postId IN (:postIds)')
                ->orWhere('d.userId = :userId')
                ->setParameter('postIds', $postIds)
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\PasswordReset', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Pin', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\TwoFactor\EmailToken', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\ActionLog', 'd')
                ->where('d.author = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\JwtRefreshToken', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\User', 'd')
                ->where('d.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\UserInfo', 'd')
                ->where('d.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Privacy', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Video\VideoHistory', 'd')
                ->where('d.userId = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()->getResult();

            $this->em->getConnection()->commit();
        }catch (\Exception $e) {
            $io->writeln("Erorr.");
            $this->em->getConnection()->rollBack();
            $io->error($e->getMessage());
        }

        return Command::SUCCESS;
    }

    function deletePosts($postIds, $userId)
    {
        $this->em->createQueryBuilder()
            ->delete('App:Media\Magnet', 'd')
            ->where('d.postId IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:Media\Poll', 'd')
            ->where('d.postId IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:Media\PollVote', 'd')
            ->where('d.postId IN (:postIds)')
            ->orWhere('d.userId = :userId')
            ->setParameter('postIds', $postIds)
            ->setParameter('userId', $userId)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:Post\Favorite', 'd')
            ->where('d.favoriter = :userId')
            ->orWhere('d.favorited = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:Media\Attachment', 'd')
            ->where('d.post IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:Post\Mention', 'd')
            ->where('d.userId = :userId')
            ->orWhere('d.causerId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:Post\TagList', 'd')
            ->where('d.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->update('App:Post\Reply', 'd')
            ->set('d.parent', ':null')
            ->where('d.post IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->setParameter('null', null)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:Post\Reply', 'd')
            ->where('d.post IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:Post\Post', 'd')
            ->where('d.id IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->getQuery()->getResult();
    }
}