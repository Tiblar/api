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

class CreateAdminMatrixUserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-admin-matrix-user';

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

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Create admin matrix user.')
            ->setHelp('Create admin matrix user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->matrixInterface->createUser(Snowflake::createSystemSnowflake());

        $io->writeln("UPDATE users set admin = 1 where name = '@fc_" . Snowflake::createSystemSnowflake() . ":matrix.sneed.supply:8008';");

        return Command::SUCCESS;
    }
}