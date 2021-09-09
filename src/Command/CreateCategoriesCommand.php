<?php
namespace App\Command;

use App\Entity\Billing\Product;
use App\Entity\Billing\ProductAttribute;
use App\Entity\Video\Category;
use App\Service\Billing\Stripe;
use App\Service\Generator\Snowflake;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCategoriesCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-categories';

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
        $this->setDescription('Creates Formerly Chuck\'s categories.')
            ->setHelp('This command creates Formerly Chuck\'s categories.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $categories = [
            "Autos & Vehicles",
            "Film & Animation",
            "Music",
            "Pets & Animals",
            "Sports",
            "Travel & Events",
            "Gaming",
            "People & Blogs",
            "Comedy",
            "News & Politics",
            "Howto & Style",
            "Cars & Vehicles",
            "Education",
            "Science & Technology",
            "Nonprofits & Activism",
            "Movies",
            "Anime/Animation",
            "Action/Adventure",
            "Classics",
            "Documentary",
            "Drama",
            "Family",
            "Horror",
            "Sci-Fi/Fantasy",
            "Thriller",
            "Shorts",
            "Shows",
            "Trailers",
        ];

        foreach($categories as $title){
            try{
                $category = new Category();
                $category->setTitle($title);
                $this->em->persist($category);
                $this->em->flush();
            }catch (\Exception $e) {

            }
        }

        $io->success("Categories created.");

        return Command::SUCCESS;
    }
}