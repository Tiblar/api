<?php
declare(strict_types=1);

namespace App\Service\Generator;

use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\EntityManager;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Snowflake extends AbstractIdGenerator
{
    /**
     * @return string
     * @throws \Exception|InvalidArgumentException
     */
    public function generate(EntityManager $em, $entity): string
    {
        return self::create();
    }

    /**
     * User ID that the system uses
     *
     * For instance for the user id of
     * system products
     *
     * @return string
     */
    public static function createSystemSnowflake(): string
    {
        return "00000000000000000000";
    }

    /**
     * Create an extremely flawed snowflake for MySQL/MariaDB
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function create(): string
    {
        $cache = new FilesystemAdapter();

        $cacheValue = $cache->getItem( 'snowflake_inc');
        if(!$cacheValue->isHit()){
            $cacheValue->set(0);
            $cache->save($cacheValue);
        }

        $cacheValue = $cache->getItem( 'snowflake_inc');

        $microtime = str_pad(
            //                                     1641024000
            decbin(round(microtime(true) * 1000) - 1420070400000),
            41,
            "0",
            STR_PAD_LEFT
        );

        // If not integer or >= 4095 set 0, else increment by 1
        $increment = str_pad(
            strval(decbin(bindec(intval($cacheValue->get())) < 4095 ? bindec(intval($cacheValue->get())) + 1 : 0)),
            12,
            "0",
            STR_PAD_LEFT
        );

        $cacheValue->set($increment);
        $cache->save($cacheValue);

        if(!ctype_digit($_ENV['FORMERLY_CHUCKS_WORKER_ID'])) throw new \Exception("You must set FORMERLY_CHUCKS_WORKER_ID environment variable.");

        $workerId = str_pad(
            strval(decbin($_ENV['FORMERLY_CHUCKS_WORKER_ID'])),
            10,
            "0",
            STR_PAD_LEFT
        );

        $snowflake = $microtime . $workerId . $increment;

        if(strlen($snowflake) !== 64 && strlen($snowflake) !== 63){
            throw new \Exception("Error generating snowflake: {$snowflake}");
        }

        $snowflake = strval(bindec($snowflake));

        return $snowflake;
    }
}