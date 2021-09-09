<?php
namespace App\Command;

use App\Entity\Billing\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MigrateFileExtensionsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:migrate-file-extensions';

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
        $this->setDescription('Make file extensions for old rows.')
            ->setHelp('This command computes the extension row to the file table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $qb = $this->em->createQueryBuilder();

        $qb->update('App:Media\File', 'f')
            ->set('f.extension', "LOWER(EXTENSION(f.hashName))")
            ->getQuery()
            ->execute();

        return Command::SUCCESS;
    }
}