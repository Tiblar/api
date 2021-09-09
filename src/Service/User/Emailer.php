<?php
namespace App\Service\User;

use App\Entity\User\Addons\ConfirmEmail;
use App\Entity\User\Addons\Disable2FAEmail;
use App\Entity\User\Addons\PasswordReset;
use App\Entity\User\TwoFactor\EmailToken;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Security;

class Emailer
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var string
     */
    private $clearnetURL;

    /**
     * @var int
     */
    private $resendRate;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer, string $clearnetURL, int $resendRate)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->clearnetURL = $clearnetURL;
        $this->resendRate = $resendRate;
    }

    public function cancelConfirmationEmail(string $userId)
    {
        $this->em->createQueryBuilder()
            ->update('App:User\User', 'u')
            ->set('u.confirmEmail', 'NULL')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getResult();

        $this->em->createQueryBuilder()
            ->delete('App:User\Addons\ConfirmEmail', 'c')
            ->where('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getResult();
    }

    public function sendConfirmationEmail(string $userId)
    {
        $confirm = $this->em->getRepository(ConfirmEmail::class)->findOneBy([
            'userId' => $userId,
        ]);

        if(!$confirm INSTANCEOF ConfirmEmail){
            throw new NotFoundHttpException("Confirm email not found.");
        }

        if($confirm->getExpireTimestamp() < new \DateTime()){

            $expireTimestamp = new \DateTime();
            $expireTimestamp->modify("+1 day");

            $confirm->setCode(\bin2hex(\openssl_random_pseudo_bytes(8)));
            $confirm->setExpireTimestamp($expireTimestamp);
        }

        $clearnetURL = $this->clearnetURL;
        $code = $confirm->getCode();

        $message = (new Email())
            ->from('noreply@formerlychucks.net')
            ->to($confirm->getEmail())
            ->subject("Confirm Email for Formerly Chuck's")
            ->html(
                "<html>" .
                "<body>" .
                "<p>Visit this URL to confirm your email:</p>" .
                "<a href='https://$clearnetURL/auth/confirm-email/$code'>" .
                    "https://$clearnetURL/auth/confirm-email/$code" .
                "</a>" .
                "</body>" .
                "</html>"
            )
            ->text(
                "Visit this URL to confirm your email:\n" .
                "https://{$this->clearnetURL}/auth/confirm-email/" . $confirm->getCode()
            );

        $this->em->flush();

        $this->mailer->send($message);
    }

    public function sendDisable2FAEmail(string $userId, User $user = null)
    {
        $disable = $this->em->getRepository(Disable2FAEmail::class)->findBy([
            'userId' => $userId,
        ], ['id' => 'DESC'], 1);

        $disable = current($disable);

        if(!$disable INSTANCEOF Disable2FAEmail){
            throw new NotFoundHttpException("Disable request not found.");
        }

        if(is_null($user)){
            $user = $this->em->getRepository(User::class)->findOneBy([
                'userId' => $userId
            ]);
        }

        if(!$user INSTANCEOF User){
            throw new NotFoundHttpException("User not found.");
        }

        $clearnetURL = $this->clearnetURL;
        $code = $disable->getCode();

        $message = (new Email())
            ->from('noreply@formerlychucks.net')
            ->to($user->getEmail())
            ->subject("Disable two factor authentication for Formerly Chuck's")
            ->html(
                "<html>" .
                "<body>" .
                "<p>There has been a request to disable two factor authentication. " .
                "If you did not make this request your account probably is compromised. You should change you password.</p>" .
                "<p>Visit this URL to remove two factor authentication:</p>" .
                "<a href='https://$clearnetURL/auth/disable-2fa/email/$code'>" .
                    "https://$clearnetURL/auth/disable-2fa/email/$code" .
                "</a>" .
                "</body>" .
                "</html>"
            )
            ->text(
                "There has been a request to disable two factor authentication. " .
                "If you did not make this request your account probably is compromised. You should change you password.\n" .
                "Visit this URL to remove two factor authentication:\n" .
                "https://$clearnetURL/auth/disable-2fa/email/$code"
            );

        $this->mailer->send($message);
    }

    public function sendLoginLinkEmail(string $userId, User $user = null)
    {
        $token = $this->em->getRepository(EmailToken::class)->findBy([
            'userId' => $userId,
        ], ['id' => 'DESC'], 1);

        $token = current($token);

        if(!$token INSTANCEOF EmailToken){
            throw new \Exception("Email login token was not generated.");
        }

        if(is_null($user)){
            $user = $this->em->getRepository(User::class)->findOneBy([
                'userId' => $userId
            ]);
        }

        if(!$user INSTANCEOF User){
            throw new NotFoundHttpException("User not found.");
        }

        $clearnetURL = $this->clearnetURL;
        $code = $token->getCode();

        $message = (new Email())
            ->from('noreply@formerlychucks.net')
            ->to($user->getEmail())
            ->subject("Login link for Formerly Chuck's")
            ->html(
                "<html>" .
                "<body>" .
                "<p>There has been a request to login. " .
                "If you did not make this request your account probably is compromised. You should change you password.</p>" .
                "<p>Visit this URL to login:</p>" .
                "<a href='https://$clearnetURL/auth/two-factor/login/$code'>" .
                "https://$clearnetURL/auth/two-factor/login/$code" .
                "</a>" .
                "</body>" .
                "</html>"
            )
            ->text(
                "There has been a request to login. " .
                "If you did not make this request your account probably is compromised. You should change you password.\n" .
                "Visit this URL to login:\n" .
                "https://$clearnetURL/auth/two-factor/login/$code"
            );

        $this->mailer->send($message);
    }

    public function sendPasswordResetEmail(string $userId, User $user = null)
    {
        $token = $this->em->getRepository(PasswordReset::class)->findBy([
            'userId' => $userId,
        ], ['id' => 'DESC'], 1);

        $token = current($token);

        if(!$token INSTANCEOF PasswordReset){
            throw new \Exception("Password reset token was not generated.");
        }

        if(is_null($user)){
            $user = $this->em->getRepository(User::class)->findOneBy([
                'userId' => $userId
            ]);
        }

        if(!$user INSTANCEOF User){
            throw new NotFoundHttpException("User not found.");
        }

        $clearnetURL = $this->clearnetURL;
        $code = $token->getCode();

        $message = (new Email())
            ->from('noreply@formerlychucks.net')
            ->to($user->getEmail())
            ->subject("Password reset for Formerly Chuck's")
            ->html(
                "<html>" .
                "<body>" .
                "<p>There has been a request to reset your password. " .
                "If you did not make this request your account probably is compromised. You should change you password.</p>" .
                "<p>Visit this URL to reset your password:</p>" .
                "<a href='https://$clearnetURL/auth/reset/$code'>" .
                "https://$clearnetURL/auth/reset/$code" .
                "</a>" .
                "</body>" .
                "</html>"
            )
            ->text(
                "There has been a request to reset your password. " .
                "If you did not make this request your account probably is compromised. You should change you password.\n" .
                "Visit this URL to reset your password:\n" .
                "https://$clearnetURL/auth/reset/$code"
            );

        $this->mailer->send($message);
    }

    public function sendFauxEmail()
    {
        // Takes about the same amount of time to send an email
        get_headers("https://api.sendgrid.com/v3/templates");
        get_headers("https://api.sendgrid.com/v3/resource");
    }
}
