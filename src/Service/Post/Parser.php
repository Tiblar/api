<?php
namespace App\Service\Post;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class Parser {

    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Parses text
     *
     * @param $text
     * @return array
     */
    public function mention(?string $text, bool $stripParagraph = false) {
        if(is_null($text)){
            return [];
        }

        if($stripParagraph){
            $text = rtrim(ltrim($text, "<p>"), "</p>");
        }

        $text = "<div>" . $text . "</div>";

        preg_match_all('/(^|\s|>)([@][a-zA-Z0-9_]+)/', $text, $results);
        $results = array_unique($results[2]);

        $usernames = preg_replace("/[^a-zA-Z0-9_]+/", "", $results);

        $dom = new \DOMDocument();
        $dom->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        foreach ($dom->getElementsByTagName('a') as $node) {
            $value = $node->nodeValue;

            preg_match_all('/(^|\s|>)([@][a-zA-Z0-9_]+)/', $value, $anchorResults);
            $anchorResults = array_unique($anchorResults[2]);

            foreach($anchorResults as $result) {
                $list = $this->strpos_all($value, $result);

                foreach($list as $pos){
                    $v1 = substr($value, 0, $pos);
                    $v2 = substr($value, $pos + 1, strlen($value));

                    $value = $v1 . $v2;
                }
            }

            $node->nodeValue = $value;
        }
        $text = $dom->saveHTML();

        $users = $this->em->getRepository(User::class)
            ->findUsers($usernames);

        $mentions = [];
        foreach($results as $result){
            $list = $this->strpos_all($text, $result);

            foreach($users as $user){
                if("@" . strtolower($user->getInfo()->getUsername()) === strtolower($result)){
                    foreach($list as $pos){
                        $mentions[] = [
                            'user_id' => $user->getId(),
                            'indices' => [$pos - 5, ($pos + strlen($result)) - 5],
                        ];
                    }
                }
            }
        }

        usort($mentions, function($a, $b) {
            return $a['indices'][0] - $b['indices'][0];
        });

        return $mentions;
    }

    private function strpos_all($haystack, $needle) {
        $offset = 0;
        $allpos = [];
        while (($pos = strpos($haystack, $needle, $offset)) !== FALSE) {
            $offset   = $pos + 1;
            $allpos[] = $pos;
        }
        return $allpos;
    }
}