<?php
namespace App\Command;

use App\Entity\Application\Application;
use App\Entity\Application\OAuth\RedirectURL;
use App\Entity\User\User;
use App\Entity\User\UserInfo;
use App\Service\Generator\Snowflake;
use App\Service\Matrix\MatrixInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CreateMatrixUsersCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-matrix-users';

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var MatrixInterface
     */
    private MatrixInterface $matrixInterface;

    /**
     * @var string
     */
    private string $matrixServer;

    public function __construct(EntityManagerInterface $em, MatrixInterface $matrix, ParameterBagInterface $params, string $name = null)
    {
        $this->em = $em;
        $this->matrixInterface = $matrix;

        // Remove for less memory usage
        $connection = $this->em->getConnection();
        $connection->getConfiguration()->setSQLLogger(null);

        $this->matrixServer = $params->get("matrix")['server'];
        //echo "matrix server [", $this->matrixServer, "]<br>\n";
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Create matrix users.')
            ->setHelp('Create matrix users for Formerly Chuck\'s users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = new \Datetime();
        $io = new SymfonyStyle($input, $output);

        $matrixUserIds = [];
        $page = 0;

        // get a list of everyone we already have
        while(true){
            $data = $this->matrixInterface->listUserIds($page);
            $page++;

            $matrixUserIds = array_unique(array_merge($matrixUserIds, $data['users']));

            if($data['end']){
                break;
            }
        }
        //print_r($matrixUserIds);

        $externalIds = "INSERT INTO user_external_ids values";
        $first = 0;
        $start = true;
        $mxcAvatars = [];

        while(true){
            // find any one we don't have, batched at 100 at a time
            $list = $this->em->createQueryBuilder()
                ->select('u', 'a')
                ->from('App:User\UserInfo', 'u')
                ->leftJoin('u.avatar', 'a')
                ->where('u.id NOT IN (:ids)')
                ->setParameter('ids', $matrixUserIds)
                ->setMaxResults(100)
                ->setFirstResult($first)
                ->getQuery()
                ->getArrayResult();

            if(count($list) === 0){
                break;
            }

            $first += 100;

            foreach ($list as $arr) {
                $user = new User([
                    'username' => $arr['username']
                ]);
                $user->setId($arr['id']);

                if(!isset($mxcAvatars[$arr['avatar']])){
                    $avatarContents = file_get_contents("https:" . $arr['avatar']);

                    $mxc = $this->matrixInterface->uploadAvatar($avatarContents);
                    $mxcAvatars[$arr['avatar']] = $mxc;
                }

                $this->matrixInterface->updateUser($user, [
                    'username' => true,
                    'avatar_mxc' => $mxcAvatars[$arr['avatar']],
                ]);

                $matrixServer = $this->matrixServer;

                if($start === false){
                    $externalIds .= ",";
                }

                $start = false;

                $externalIds .= " ('oidc-formerly-chucks', '" . $arr['id'] . "', '@fc_" . $arr['id'] . ":" . $matrixServer . "')";
            }
        }

        $externalIds .= ";";
        $io->write($externalIds);

        $endTime = new \DateTime();

        $seconds = $endTime->getTimestamp() - $startTime->getTimestamp();

        $io->write("/* Complete in " . $seconds . " seconds. */");
        $io->writeln("");

        return Command::SUCCESS;
    }
}