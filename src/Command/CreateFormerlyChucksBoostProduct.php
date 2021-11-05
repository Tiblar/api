<?php
namespace App\Command;

use App\Entity\Billing\Product;
use App\Entity\Billing\ProductAttribute;
use App\Service\Billing\Stripe;
use App\Service\Generator\Snowflake;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateFormerlyChucksBoostProduct extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-formerly-chucks-boost-product';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Stripe
     */
    private $stripe;

    public function __construct(EntityManagerInterface $em, Stripe $stripe, string $name = null)
    {
        $this->em = $em;
        $this->stripe = $stripe;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Creates a new Formerly Chuck\'s boost product.')
            ->setHelp('This command allows you to create a new Formerly Chuck\'s boost product.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $product = new Product();
        $this->em->persist($product);

        $annualDiscount = 15;

        $basePrice = 2.95;
        $baseTitle = "Formerly Chuck's Boost";
        $baseDescription = "Support Formerly Chuck's and get extra storage, increased file sizes, and more.";

        $storagePrice = 1;
        $storageTitle = "Storage (100gb)";
        $storageDescription = "100gb additional storage";

        $currency = Product::$CUR_USD;

        $attribute = new ProductAttribute();
        $this->em->persist($attribute);

        /**
        $stripeProduct = $this->stripe->createProduct($product->getId(), $baseTitle, $baseDescription);

        $recurring = [
            'interval' => 'month',
        ];

        $stripePrice = $this->stripe->createPrice(
            $stripeProduct->id,
            $currency,
            $basePrice,
            $baseTitle,
            $recurring
        );

        $recurring = [
            'interval' => 'year',
        ];

        $annualPrice = ($basePrice * (1-($annualDiscount/100))) * 12;
        $stripePriceAnnualDiscount = $this->stripe->createPrice(
            $stripeProduct->id,
            $currency,
            $annualPrice,
            $baseTitle . " (annual)",
            $recurring
        );


        $recurring = [
            'interval' => 'month',
        ];

        $stripePriceAttribute = $this->stripe->createPrice(
            $stripeProduct->id,
            $currency,
            $storagePrice,
            $storageTitle,
            $recurring
        );

        $recurring = [
            'interval' => 'year',
        ];

        $annualPrice = ($storagePrice * (1-($annualDiscount/100))) * 12;
        $stripePriceAnnualDiscountAttribute = $this->stripe->createPrice(
            $stripeProduct->id,
            $currency,
            $annualPrice,
            $storageTitle . " (annual)",
            $recurring
        );
        **/

        $attribute->setUserId(Snowflake::createSystemSnowflake());
        $attribute->setProduct($product);
        $attribute->setTitle($storageTitle);
        $attribute->setDescription($storageDescription);
        $attribute->setPrice($storagePrice);
        $attribute->setValue(100);
        $attribute->setMinQuantity(1);
        $attribute->setMaxQuantity(80);

        /**
        $attribute->setStripePriceId($stripePriceAttribute->id);
        $attribute->setStripePriceAnnualDiscountId($stripePriceAnnualDiscountAttribute->id);
        **/

        $product->setTitle($baseTitle);
        $product->setDescription($baseDescription);
        $product->setCurrency($currency);
        $product->setPrice($basePrice);
        $product->setAnnualDiscount($annualDiscount);
        $product->addSubscriptionFrequency(Product::$DUR_MONTHLY);
        $product->addSubscriptionFrequency(Product::$DUR_ANNUALLY);
        $product->setShipping(false);
        $product->setPublished(true);
        $product->setUserLimit(null);
        $product->addAttribute($attribute);

        /**
        $product->setStripeProductId($stripeProduct->id);
        $product->setStripePriceId($stripePrice->id);
        $product->setStripePriceAnnualDiscountId($stripePriceAnnualDiscount->id);
        **/

        $this->em->flush();

        $io->success("Boost created.");

        $io->success([
            "Boost Product ID: " . $product->getId(),
            "Storage Attribute ID: " . $attribute->getId(),
        ]);

        return Command::SUCCESS;
    }
}