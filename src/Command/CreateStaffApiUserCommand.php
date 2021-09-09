<?php
namespace App\Command;

use App\Entity\Staff\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateStaffApiUserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-staff-api-user';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, string $name = null)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Creates a new staff api user.')
            ->setHelp('This command allows you to create a new staff api user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $username = bin2hex(openssl_random_pseudo_bytes(32));;
        $password = bin2hex(openssl_random_pseudo_bytes(32));;

        $staffUser = new User();
        $staffUser->setUsername($username);
        $staffUser->addRole("ROLE_STAFF_API");
        $staffUser->setPassword($password);

        $encodedPassword = $this->passwordEncoder->encodePassword($staffUser, $password);
        $staffUser->setPassword($encodedPassword);

        $this->em->persist($staffUser);
        $this->em->flush();

        $io->success("User created.");

        $io->success([
            "Username: " . $username,
            "Password: " . $password
        ]);

        return Command::SUCCESS;
    }
}