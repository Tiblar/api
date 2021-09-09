<?php
namespace App\Command;

use App\Entity\Application\Application;
use App\Entity\Application\OAuth\RedirectURL;
use App\Service\Generator\Snowflake;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateOAuthMatrixCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-oauth-matrix-application';

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
        $this->setDescription('Creates a new application for Matrix.')
            ->setHelp('This command allows you to create a new application for matrix.');


        $this
            ->addArgument('url', InputArgument::REQUIRED, 'Enter redirect URL.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $url = $input->getArgument('url');
        if(!filter_var($url, FILTER_VALIDATE_URL)){
            $io->error("Invalid url.");

            return Command::FAILURE;
        }

        $application = new Application();
        $application->setName("Formerly Chuck's");
        $application->setDescription("Authenticate Matrix.");
        $application->setUserId(Snowflake::createSystemSnowflake());

        $this->em->persist($application);

        $redirectURL = new RedirectURL();
        $redirectURL->setClient($application);
        $redirectURL->setURL($url);
        $this->em->persist($redirectURL);

        $application->addRedirectURL($redirectURL);

        $this->em->flush();

        $io->success("Application created.");

        $io->success([
            "Client ID: " . $application->getId(),
            "Client Secret: " . $application->getClientSecret(),
        ]);

        return Command::SUCCESS;
    }
}