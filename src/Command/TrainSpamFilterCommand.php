<?php
namespace App\Command;

use App\Entity\Billing\Invoice;
use App\Entity\Billing\Product;
use App\Service\SpamFilter\SpamFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TrainSpamFilterCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:train-spam-filter';

    /**
     * @var SpamFilter
     */
    private SpamFilter $spamFilter;

    public function __construct(SpamFilter $spamFilter, string $name = null)
    {
        $this->spamFilter = $spamFilter;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Train spam filter.')
            ->setHelp('This command trains the spam filter.');

        $this
            ->addArgument('mode', InputArgument::REQUIRED, 'Modes: train, test')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $mode = $input->getArgument('mode');

        if(strtolower($mode) === "train"){
            $this->train($io);
        }

        if(strtolower($mode) === "test"){
            $this->test($io);
        }

        return Command::SUCCESS;
    }

    private function train($io) {
        $contents = file_get_contents(__DIR__ . "/data/ham.txt");
        $this->spamFilter->learn($contents, null, SpamFilter::HAM);

        $contents = file_get_contents(__DIR__ . "/data/spam.txt");
        $this->spamFilter->learn($contents, null, SpamFilter::SPAM);
    }

    private function test($io) {
        $contents = file_get_contents(__DIR__ . "/data/filter_test.txt");
        $io->writeln($this->spamFilter->classify($contents));
    }
}