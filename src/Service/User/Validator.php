<?php
namespace App\Service\User;

use App\Entity\User\User;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\EntityManagerInterface;

class Validator
{
    static $USERNAME_TAKEN_ERROR = "This user already exists.";
    static $USERNAME_CHAR_ERROR = "Username must be alphanumeric with optional underscores.";
    static $USERNAME_LENGTH_ERROR = "Username must be less than 16 and more than 3 characters.";

    static $EMAIL_INVALID_ERROR = "Invalid email address.";
    static $EMAIL_TAKEN_ERROR = "This email is already being used.";

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $username
     * @return bool|string
     */
    public function username($username)
    {
        if(
            is_null($username) || empty(str_replace("_", "", $username))
            || !ctype_alnum(str_replace("_", "", $username))
        ) {
            return self::$USERNAME_CHAR_ERROR;
        }

        if(strlen($username) > 16 || strlen($username) < 3){
            return self::$USERNAME_LENGTH_ERROR;
        }

        $users = $this->em->createQueryBuilder()
            ->select('i')
            ->from('App:User\UserInfo', 'i')
            ->where('i.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getResult();

        if(count($users) > 0){
            return self::$USERNAME_TAKEN_ERROR;
        }

        return true;
    }

    /**
     * @param $email
     * @param bool $unique
     * @return bool|string
     */
    public function email($email, $unique = true)
    {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return self::$EMAIL_INVALID_ERROR;
        }

        $filter = explode('@', $email);
        $domain = array_pop($filter);

        if(!preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $domain)){
            return self::$EMAIL_INVALID_ERROR;
        }

        if($unique){
            $users = $this->em->createQueryBuilder()
                ->select('u')
                ->from('App:User\User', 'u')
                ->where('u.email = :email')
                ->setParameter('email', $email)
                ->getQuery()
                ->getResult();

            if(count($users) > 0){
                return self::$EMAIL_TAKEN_ERROR;
            }
        }

        return true;
    }
}
