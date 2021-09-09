<?php
namespace App\Service\SpamFilter;

use App\Entity\SpamFilter\IpList;
use App\Service\SpamFilter\Degenerator\Degenerator;
use App\Service\SpamFilter\Lexer\Lexer;
use App\Service\SpamFilter\Storage\MySQL;
use Doctrine\ORM\EntityManagerInterface;

class SpamFilter
{
    const SPAM    = 'spam';
    const HAM     = 'ham';
    const LEARN   = 'learn';
    const UNLEARN = 'unlearn';

    const KEY_COUNT_HAM  = 'count_ham';
    const KEY_COUNT_SPAM = 'count_spam';
    const KEY_TEXTS_HAM  = 'texts_ham';
    const KEY_TEXTS_SPAM = 'texts_spam';

    private array $config = [
        'use_relevant' => 15,
        'min_dev'      => 0.2,
        'rob_s'        => 0.3,
        'rob_x'        => 0.5
    ];

    private MySQL $storage;
    private Lexer $lexer;
    private Degenerator $degenerator;

    private array $token_data;

    private EntityManagerInterface $em;

    private string $spamKey;

    /**
     * Constructs b8
     *
     * @throws \Exception
     * @return void
     */
    function __construct(EntityManagerInterface $em, string $spamKey)
    {
        $this->degenerator = new Degenerator([]);
        $this->lexer = new Lexer([]);
        $this->storage = new MySQL($this->degenerator, $em);

        $this->em = $em;
        $this->spamKey = $spamKey;
    }

    /**
     * Classifies a text
     *
     * @access public
     * @param string The text to classify
     * @return float The rating between 0 (ham) and 1 (spam)
     * @throws \Exception
     */
    public function classify(string $text, ?string $clientIp)
    {
        $text = mb_strtolower($text);

        // Get the internal database variables, containing the number of ham and spam texts so the
        // spam probability can be calculated in relation to them
        $internals = $this->storage->get_internals();

        // Calculate the spaminess of all tokens

        // Get all tokens we want to rate
        $tokens = $this->lexer->get_tokens($text);

        // Check if the lexer failed (if so, $tokens will be a lexer error code, if not, $tokens
        //  will be an array)
        if(!is_array($tokens)){
            throw new \Exception("Tokens are invalid.");
        }

        // Fetch all available data for the token set from the database
        $this->token_data = $this->storage->get(array_keys($tokens));

        // Calculate the spaminess and importance for each token (or a degenerated form of it)

        $word_count = [];
        $rating     = [];
        $importance = [];

        foreach ($tokens as $word => $count) {
            $word_count[$word] = $count;

            // Although we only call this function only here ... let's do the calculation stuff in a
            // function to make this a bit less confusing ;-)
            $rating[$word] = $this->get_probability($word, $internals);
            $importance[$word] = abs(0.5 - $rating[$word]);
        }

        // Order by importance
        arsort($importance);
        reset($importance);

        // Get the most interesting tokens (use all if we have less than the given number)
        $relevant = [];
        for ($i = 0; $i < $this->config['use_relevant']; $i++) {
            if ($token = key($importance)) {
                // Important tokens remain

                // If the token's rating is relevant enough, use it
                if (abs(0.5 - $rating[$token]) > $this->config['min_dev']) {
                    // Tokens that appear more than once also count more than once
                    for ($x = 0, $l = $word_count[$token]; $x < $l; $x++) {
                        array_push($relevant, $rating[$token]);
                    }
                }
            } else {
                // We have less words as we want to use, so we already use what we have and can
                // break here
                break;
            }

            next($importance);
        }

        // Calculate the spaminess of the text (thanks to Mr. Robinson ;-)

        // We set both haminess and spaminess to 1 for the first multiplying
        $haminess  = 1;
        $spaminess = 1;

        // Consider all relevant ratings
        foreach ($relevant as $value) {
            $haminess  *= (1.0 - $value);
            $spaminess *= $value;
        }

        // If no token was good for calculation, we really don't know how to rate this text, so
        // we can return 0.5 without further calculations.
        if ($haminess == 1 && $spaminess == 1) {
            return 0.5;
        }

        // Calculate the combined rating

        // Get the number of relevant ratings
        $n = count($relevant);

        // The actual haminess and spaminess
        $haminess  = 1 - pow($haminess,  (1 / $n));
        $spaminess = 1 - pow($spaminess, (1 / $n));

        // Calculate the combined indicator
        $probability = ($haminess - $spaminess) / ($haminess + $spaminess);

        // We want a value between 0 and 1, not between -1 and +1, so ...
        $probability = (1 + $probability) / 2;

        if(strlen($text) > 2000){
            $probability += 0.1;
        }

        if(strlen($text) > 1000){
            $probability += 0.05;
        }

        if(strlen($text) < 100){
            $probability -= 0.5;
        }

        if(strlen($text) < 150){
            $probability -= 0.2;
        }

        if(str_contains($text, "social pages")){
            $probability += 0.25;
        }

        if(str_contains($text, "plumbing")){
            $probability += 0.1;
        }

        if(str_contains($text, "personal injury")){
            $probability += 0.1;
        }

        if(str_contains($text, "law firm")){
            $probability += 0.1;
        }

        if(str_contains($text, ", llc") || str_contains($text, ", llp")){
            $probability += 0.1;
        }

        if(str_contains($text, "water damage")){
            $probability += 0.1;
        }

        if(str_contains($text, "tiblar")){
            $probability += 0.2;
        }

        if($probability > 1){
            $probability = 1;
        }

        if($probability < 0){
            $probability = 0;
        }

        if(!is_null($clientIp) && is_string($clientIp)){
            $ip = $this->em->getRepository(IpList::class)->findOneBy([
                'ipAddress' => $clientIp,
            ]);

            if($ip instanceof IpList){
                $probability += $ip->getRating();
            }
        }

        // Alea iacta est
        return $probability;
    }

    /**
     * Calculate the spaminess of a single token also considering "degenerated" versions
     *
     * @access private
     * @param string The word to rate
     * @param array The "internals" array
     * @return float The word's rating
     */
    private function get_probability(string $word, array $internals)
    {
        // Let's see what we have!
        if (isset($this->token_data['tokens'][$word])) {
            // The token is in the database, so we can use it's data as-is and calculate the
            // spaminess of this token directly
            return $this->calculate_probability($this->token_data['tokens'][$word], $internals);
        }

        // The token was not found, so do we at least have similar words?
        if (isset($this->token_data['degenerates'][$word])) {
            // We found similar words, so calculate the spaminess for each one and choose the most
            // important one for the further calculation

            // The default rating is 0.5 simply saying nothing
            $rating = 0.5;

            foreach ($this->token_data['degenerates'][$word] as $degenerate => $count) {
                // Calculate the rating of the current degenerated token
                $rating_tmp = $this->calculate_probability($count, $internals);

                // Is it more important than the rating of another degenerated version?
                if(abs(0.5 - $rating_tmp) > abs(0.5 - $rating)) {
                    $rating = $rating_tmp;
                }
            }

            return $rating;
        } else {
            // The token is really unknown, so choose the default rating for completely unknown
            // tokens. This strips down to the robX parameter so we can cheap out the freaky math
            // ;-)
            return $this->config['rob_x'];
        }
    }

    /**
     * Do the actual spaminess calculation of a single token
     *
     * @access private
     * @param array The token's data [ \b8\b8::KEY_COUNT_HAM  => int,
                                       \b8\b8::KEY_COUNT_SPAM => int ]
     * @param array The "internals" array
     * @return float The rating
     */
    private function calculate_probability(array $data, array $internals)
    {
        // Calculate the basic probability as proposed by Mr. Graham

        // But: consider the number of ham and spam texts saved instead of the number of entries
        // where the token appeared to calculate a relative spaminess because we count tokens
        // appearing multiple times not just once but as often as they appear in the learned texts.

        $rel_ham = $data[self::KEY_COUNT_HAM];
        $rel_spam = $data[self::KEY_COUNT_SPAM];

        if ($internals[self::KEY_TEXTS_HAM] > 0) {
            $rel_ham = $data[self::KEY_COUNT_HAM] / $internals[self::KEY_TEXTS_HAM];
        }

        if ($internals[self::KEY_TEXTS_SPAM] > 0) {
            $rel_spam = $data[self::KEY_COUNT_SPAM] / $internals[self::KEY_TEXTS_SPAM];
        }

        $rating = $rel_spam / ($rel_ham + $rel_spam);

        // Calculate the better probability proposed by Mr. Robinson
        $all = $data[self::KEY_COUNT_HAM] + $data[self::KEY_COUNT_SPAM];
        return (($this->config['rob_s'] * $this->config['rob_x']) + ($all * $rating))
               / ($this->config['rob_s'] + $all);
    }

    /**
     * Learn a reference text
     *
     * @return void
     * @throws \Exception
     */
    public function learn(string $text, ?string $ipAddress, string $category)
    {
        $text = mb_strtolower($text);

        if($category === self::SPAM && !is_null($ipAddress)){
            # $hashedIp = hash_hmac("sha512", $ipAddress, $this->spamKey);
            $hashedIp = $ipAddress;

            $ip = $this->em->getRepository(IpList::class)->findOneBy([
               'ipAddress' => $hashedIp
            ]);

            if(!($ip instanceof IpList)){
                $ip = new IpList();
                $ip->setIp($hashedIp);
                $ip->setRating(0.15);

                $this->em->persist($ip);
            }else{
                $ip->setRating($ip->getRating() + 0.15);
            }

            $ip->setUpdatedTimestamp(new \DateTime());
            $this->em->flush();
        }

        $this->process_text($text, $category, self::LEARN);
    }

    /**
     * Unlearn a reference text
     *
     * @return void
     * @throws \Exception
     */
    public function unlearn(string $text, string $category)
    {
        $text = mb_strtolower($text);

        $this->process_text($text, $category, self::UNLEARN);
    }

    /**
     * Does the actual interaction with the storage backend for learning or unlearning texts
     *
     * @param string The text to process
     * @param string Either SpamFilter::SPAM or SpamFilter::HAM
     * @param string Either SpamFilter::LEARN or SpamFilter::UNLEARN
     * @return void
     * @throws \Exception
     */
    private function process_text(string $text, string $category, string $action)
    {
        // Look if the request is okay
        if($category !== self::HAM && $category !== self::SPAM){
            throw new \Exception("Category must be SPAM or HAM");
        }

        // Get all tokens from $text
        $tokens = $this->lexer->get_tokens($text);

        // Check if the lexer failed (if so, $tokens will be a lexer error code, if not, $tokens
        //  will be an array)
        if(!is_array($tokens)){
            throw new \Exception("Tokens are invalid.");
        }

        // Pass the tokens and what to do with it to the storage backend
        $this->storage->process_text($tokens, $category, $action);
    }
}
