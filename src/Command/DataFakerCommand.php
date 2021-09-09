<?php
namespace App\Command;

use App\Entity\Post\Favorite;
use App\Entity\Post\Post;
use App\Entity\Post\Reply;
use App\Entity\Post\TagList;
use App\Entity\User\Addons\Follow;
use App\Entity\User\Addons\Notification;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DataFakerCommand extends Command
{
    protected static $defaultName = 'app:data-faker';
    protected static $modes = ['users', 'posts', 'reblogs', 'favorites', 'replies', 'tags', 'notifications', 'followers'];
    private static $batch = 200;
    private const AVATAR = "//f000.backblazeb2.com/file/tbrcdn-test/12921fbcb398c5ee77edd538afbf21fc8c42e44479c8bb9999909bcc800163e9.png";

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine')->getManager();

        parent::__construct();
    }



    protected function configure()
    {
        $this->setDescription('Generates test data.')
            ->setHelp('This command generates test data.');

        $modes = implode(', ', self::$modes);

        $this
            ->addArgument('mode', InputArgument::REQUIRED, 'Modes: ' . $modes)
            ->addArgument('amount', InputArgument::REQUIRED, 'Amount of entities to generate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $mode = $input->getArgument('mode');
        if(!in_array($mode, self::$modes)){
            $io->error("Invalid mode.");

            return Command::FAILURE;
        }

        $amount = $input->getArgument('amount');
        if(!ctype_digit($amount)){
            $io->error("Invalid amount.");

            return Command::FAILURE;
        }

        switch($mode){
            case "users":
                $this->users($io, $amount);
                break;
            case "posts":
                $this->posts($io, $amount);
                break;
            case "reblogs":
                $this->reblogs($io, $amount);
                break;
            case "favorites":
                $this->favorites($io, $amount);
                break;
            case "replies":
                $this->replies($io, $amount);
                break;
            case "tags":
                $this->tags($io, $amount);
                break;
            case "notifications":
                $this->notifications($io, $amount);
                break;
            case "followers":
                $this->followers($io, $amount);
                break;
        }

        return Command::SUCCESS;
    }

    private function users(SymfonyStyle $io, int $amount)
    {
        $generator = \Faker\Factory::create();

        for($i=0;$i<$amount;$i++) {
            $random = base64_encode(openssl_random_pseudo_bytes(64));
            $user = new User(['username' => substr(str_replace('.', '', $generator->userName) . $random, 0, 32)]);
            $info = $user->getInfo();

            $user->setEmail(rand(0, 100) . $generator->email);
            $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
            $this->em->persist($user);

            $info->setId($user->getId());
            $info->setAvatar(self::AVATAR);
            $info->setBiography($generator->title);
            $info->setLocation($generator->state);
            $this->em->persist($info);

            if ($i % self::$batch === 0) {
                $left = strval($amount - $i);
                $io->writeln("Writing to database... $left left");

                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();

        $io->success("Created $amount User entities.");

        return Command::SUCCESS;
    }

    private function posts(SymfonyStyle $io, int $amount)
    {
        $generator = \Faker\Factory::create();

        $authors = $this->em->getRepository(User::class)->findBy([], [], 40);

        if(empty($authors)){
            $io->error("There are no users in the database. Run the users mode first.");

            return Command::FAILURE;
        }

        for($i=0;$i<$amount;$i++) {
            $randAuthor = array_rand($authors);
            $author = $authors[$randAuthor];

            $post = new Post();
            $post->setAuthor($author);
            $post->setBody($i . " - " . $generator->word());
            $post->setNsfw(false);
            $this->em->persist($post);

            if ($i % self::$batch === 0) {
                $left = strval($amount - $i);
                $io->writeln("Writing to database... $left left");

                $this->em->flush();
                $this->em->clear();

                $authors = $this->em->getRepository(User::class)->findBy([], [], 40);
            }
        }

        $this->em->flush();
        $this->em->clear();

        $io->success("Created $amount Post entities.");

        return Command::SUCCESS;
    }


    private function reblogs(SymfonyStyle $io, int $amount)
    {
        $generator = \Faker\Factory::create();

        $authors = $this->em->getRepository(User::class)->findBy([], [], 40);

        if(empty($authors)){
            $io->error("There are no users in the database. Run the user mode first.");

            return Command::FAILURE;
        }

        $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);

        if(empty($posts)){
            $io->error("There are no posts in the database. Run the posts mode first.");

            return Command::FAILURE;
        }

        for($i=0;$i<$amount;$i++) {
            $randAuthor = array_rand($authors);
            $author = $authors[$randAuthor];

            $randPost = array_rand($posts);
            $post = $posts[$randPost];

            $reblog = new Post();
            $reblog->setAuthor($author);
            $reblog->setNsfw(false);

            if($generator->boolean(15)){
                $post->setBody($i . " - " . $generator->word());
            }

            $reblog->setReblog($post);
            $this->em->persist($reblog);

            $post->setReblogsCount($post->getReblogsCount() + 1);

            if ($i % self::$batch === 0) {
                $left = strval($amount - $i);
                $io->writeln("Writing to database... $left left");

                $this->em->flush();
                $this->em->clear();

                $authors = $this->em->getRepository(User::class)->findBy([], [], 40);
                $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);
            }
        }

        $this->em->flush();
        $this->em->clear();

        $io->success("Created $amount Post (reblog) entities.");

        return Command::SUCCESS;
    }

    private function favorites(SymfonyStyle $io, int $amount)
    {
        $users = $this->em->getRepository(User::class)->findBy([], [], 40);

        if(empty($users)){
            $io->error("There are no users in the database. Run the user mode first.");

            return Command::FAILURE;
        }

        $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);

        if(empty($posts)){
            $io->error("There are no posts in the database. Run the posts mode first.");

            return Command::FAILURE;
        }

        for($i=0;$i<$amount;$i++) {
            $randUsers = array_rand($users);
            $user = $users[$randUsers];

            $randPost = array_rand($posts);
            $post = $posts[$randPost];

            $favorite = new Favorite();
            $favorite->setFavorited($post->getAuthor()->getId());
            $favorite->setFavoriter($user->getId());
            $favorite->setPost($post->getId());

            $this->em->persist($favorite);

            $post->setFavoritesCount($post->getFavoritesCount() + 1);

            if ($i % self::$batch === 0) {
                $left = strval($amount - $i);
                $io->writeln("Writing to database... $left left");

                $this->em->flush();
                $this->em->clear();

                $users = $this->em->getRepository(User::class)->findBy([], [], 40);
                $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);
            }
        }

        $this->em->flush();
        $this->em->clear();

        $io->success("Created $amount Favorite entities.");

        return Command::SUCCESS;
    }

    private function replies(SymfonyStyle $io, int $amount)
    {
        $generator = \Faker\Factory::create();

        $authors = $this->em->getRepository(User::class)->findBy([], [], 40);

        if(empty($authors)){
            $io->error("There are no users in the database. Run the user mode first.");

            return Command::FAILURE;
        }

        $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);

        if(empty($posts)){
            $io->error("There are no posts in the database. Run the posts mode first.");

            return Command::FAILURE;
        }

        for($i=0;$i<$amount;$i++) {
            $randAuthor = array_rand($authors);
            $author = $authors[$randAuthor];

            $randPost = array_rand($posts);
            $post = $posts[$randPost];

            $reply = new Reply();
            $reply->setPost($post);
            $reply->setBody($generator->title);
            $reply->setAuthor($author);

            $this->em->persist($reply);

            $post->setRepliesCount($post->getRepliesCount() + 1);

            if ($i % self::$batch === 0) {
                $left = strval($amount - $i);
                $io->writeln("Writing to database... $left left");

                $this->em->flush();
                $this->em->clear();

                $authors = $this->em->getRepository(User::class)->findBy([], [], 40);
                $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);
            }
        }

        $this->em->flush();
        $this->em->clear();

        $io->success("Created $amount Reply entities.");

        return Command::SUCCESS;
    }

    private function tags(SymfonyStyle $io, int $amount)
    {
        $generator = \Faker\Factory::create();

        $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);

        if(empty($posts)){
            $io->error("There are no posts in the database. Run the posts mode first.");

            return Command::FAILURE;
        }

        for($i=0;$i<$amount;$i++) {
            $randPost = array_rand($posts);
            $post = $posts[$randPost];

            $tag = new TagList();
            $tag->setPost($post->getId());
            $tag->setTag("NA");
            $tag->setTitle($generator->title);

            $this->em->persist($tag);

            if ($i % self::$batch === 0) {
                $left = strval($amount - $i);
                $io->writeln("Writing to database... $left left");

                $this->em->flush();
                $this->em->clear();

                $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);
            }
        }

        $this->em->flush();
        $this->em->clear();

        $io->success("Created $amount TagList entities.");

        return Command::SUCCESS;
    }

    private function notifications(SymfonyStyle $io, int $amount)
    {
        $generator = \Faker\Factory::create();

        $authors = $this->em->getRepository(User::class)->findBy([], [], 40);

        if(empty($authors)){
            $io->error("There are no users in the database. Run the user mode first.");

            return Command::FAILURE;
        }

        $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);

        if(empty($posts)){
            $io->error("There are no posts in the database. Run the posts mode first.");

            return Command::FAILURE;
        }

        for($i=0;$i<$amount;$i++) {
            $randAuthor = array_rand($authors);
            $author = $authors[$randAuthor];

            $randPost = array_rand($posts);
            $post = $posts[$randPost];

            $notification = new Notification();
            $notification->setType(Notification::$TYPE_FAVORITE);
            $notification->setPostId($post->getId());
            $notification->setUserId("RANDOM");
            $notification->addCauser($author);

            $this->em->persist($notification);

            if ($i % self::$batch === 0) {
                $left = strval($amount - $i);
                $io->writeln("Writing to database... $left left");

                $this->em->flush();
                $this->em->clear();

                $authors = $this->em->getRepository(User::class)->findBy([], [], 40);
                $posts = $this->em->getRepository(Post::class)->findBy([], [], 40);
            }
        }

        $this->em->flush();
        $this->em->clear();

        $io->success("Created $amount Notification entities.");

        return Command::SUCCESS;
    }

    private function followers(SymfonyStyle $io, int $amount)
    {
        $generator = \Faker\Factory::create();

        $users = $this->em->getRepository(User::class)->findBy([], [], 1000, 3250);

        if(empty($users)){
            $io->error("There are no posts in the database. Run the posts mode first.");

            return Command::FAILURE;
        }

        for($i=0;$i<$amount;$i++) {
            $follower = $users[$i];
            $followed = $users[$i];

            $follow = new Follow();
            $follow->setFollowedId("810669911759132887");
            $follow->setFollowerId($follower->getId());

            $this->em->persist($follow);

            if ($i % 1 === 0) {
                $left = strval($amount - $i);
                $io->writeln("Writing to database... $left left");

                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();

        $io->success("Created $amount Follower entities.");

        return Command::SUCCESS;
    }
}