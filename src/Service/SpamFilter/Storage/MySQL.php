<?php
namespace App\Service\SpamFilter\Storage;

use App\Entity\SpamFilter\WordList;
use App\Service\SpamFilter\Degenerator\Degenerator;
use App\Service\SpamFilter\SpamFilter;
use Doctrine\ORM\EntityManagerInterface;

class MySQL
{
    protected Degenerator $degenerator;

    protected EntityManagerInterface $em;

    protected function fetch_token_data(array $tokens)
    {
        $data = [];

        $tokens = $this->em->createQueryBuilder()
            ->select('w')
            ->from(WordList::class, 'w')
            ->where('w.token in (:tokens)')
            ->setParameter('tokens', $tokens)
            ->getQuery()
            ->getArrayResult();

        foreach($tokens as $token){
            $data[$token['token']] = [
                SpamFilter::KEY_COUNT_HAM  => $token['countHam'],
                SpamFilter::KEY_COUNT_SPAM => $token['countSpam']
            ];
        }

        return $data;
    }

    protected function add_token(string $token, array $count)
    {
        $word = new WordList();
        $word->setToken($token);
        $word->setCountHam($count[SpamFilter::KEY_COUNT_HAM]);
        $word->setCountSpam($count[SpamFilter::KEY_COUNT_SPAM]);

        $this->em->persist($word);
        $this->em->flush();
        $this->em->clear();
    }

    protected function update_token(string $token, array $count)
    {
        $this->em->createQueryBuilder()
            ->update(WordList::class, 'w')
            ->where('w.token = :token')
            ->set('w.countHam', $count[SpamFilter::KEY_COUNT_HAM])
            ->set('w.countSpam', $count[SpamFilter::KEY_COUNT_SPAM])
            ->setParameter('token', $token)
            ->getQuery()
            ->execute();
    }

    protected function delete_token(string $token)
    {
        $this->em->createQueryBuilder()
            ->update(WordList::class, 'w')
            ->where('w.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getResult();
    }

    /**
     * Passes the degenerator to the instance and calls the backend setup
     *
     * @param Degenerator $degenerator
     * @param EntityManagerInterface $em
     */
    public function __construct(Degenerator $degenerator, EntityManagerInterface $em)
    {
        $this->degenerator = $degenerator;
        $this->em = $em;
    }
    /**
     * Get the database's internal variables.
     *
     * @access public
     * @return array Returns an array of all internals.
     */
    public function get_internals()
    {
        $tokens = $this->em->createQueryBuilder()
            ->select('SUM(w.countHam) as ham, SUM(w.countSpam) as spam')
            ->from(WordList::class, 'w')
            ->getQuery()
            ->getArrayResult()[0];


        return [
            SpamFilter::KEY_TEXTS_HAM  => $tokens['ham'],
            SpamFilter::KEY_TEXTS_SPAM => $tokens['spam'],
        ];
    }

    /**
     * Get all data about a list of tokens from the database.
     *
     * @param array The tokens list
     * @return mixed Returns False on failure, otherwise returns array of returned data
    in the format [ 'tokens'      => [ token => count ],
    'degenerates' => [ token => [ degenerate => count ] ] ].
     */
    public function get(array $tokens)
    {
        // First we see what we have in the database
        $token_data = $this->fetch_token_data($tokens);

        // Check if we have to degenerate some tokens
        $missing_tokens = array();
        foreach ($tokens as $token) {
            if (! isset($token_data[$token])) {
                $missing_tokens[] = $token;
            }
        }

        if (count($missing_tokens) > 0) {
            // We have to degenerate some tokens
            $degenerates_list = [];

            // Generate a list of degenerated tokens for the missing tokens ...
            $degenerates = $this->degenerator->degenerate($missing_tokens);

            // ... and look them up
            foreach ($degenerates as $token => $token_degenerates) {
                $degenerates_list = array_merge($degenerates_list, $token_degenerates);
            }

            $token_data = array_merge($token_data, $this->fetch_token_data($degenerates_list));
        }

        // Here, we have all available data in $token_data.

        $return_data_tokens = [];
        $return_data_degenerates = [];

        foreach ($tokens as $token) {
            if (isset($token_data[$token])) {
                // The token was found in the database
                $return_data_tokens[$token] = $token_data[$token];
            } else {
                // The token was not found, so we look if we can return data for degenerated tokens
                foreach ($this->degenerator->degenerates[$token] as $degenerate) {
                    if (isset($token_data[$degenerate])) {
                        // A degenertaed version of the token way found in the database
                        $return_data_degenerates[$token][$degenerate] = $token_data[$degenerate];
                    }
                }
            }
        }

        // Now, all token data directly found in the database is in $return_data_tokens  and all
        // data for degenerated versions is in $return_data_degenerates, so
        return [ 'tokens'      => $return_data_tokens,
            'degenerates' => $return_data_degenerates ];
    }

    /**
     * Stores or deletes a list of tokens from the given category.
     *
     * @access public
     * @param array The tokens list
     * @param string Either SpamFilter::HAM or SpamFilter::SPAM
     * @param string Either SpamFilter::LEARN or SpamFilter::UNLEARN
     * @return void
     */
    public function process_text(array $tokens, string $category, string $action)
    {
        // No matter what we do, we first have to check what data we have.

        // Then, fetch all data for all tokens we have
        $token_data = $this->fetch_token_data(array_keys($tokens));

        $this->em->getConnection()->beginTransaction();

        try{
            // Process all tokens to learn/unlearn
            foreach ($tokens as $token => $count) {
                if (isset($token_data[$token])) {
                    // We already have this token, so update it's data

                    // Get the existing data
                    $count_ham  = $token_data[$token][SpamFilter::KEY_COUNT_HAM];
                    $count_spam = $token_data[$token][SpamFilter::KEY_COUNT_SPAM];

                    // Increase or decrease the right counter
                    if ($action === SpamFilter::LEARN) {
                        if ($category === SpamFilter::HAM) {
                            $count_ham += $count;
                        } elseif ($category === SpamFilter::SPAM) {
                            $count_spam += $count;
                        }
                    } elseif ($action == SpamFilter::UNLEARN) {
                        if ($category === SpamFilter::HAM) {
                            $count_ham -= $count;
                        } elseif ($category === SpamFilter::SPAM) {
                            $count_spam -= $count;
                        }
                    }

                    // We don't want to have negative values
                    if ($count_ham < 0) {
                        $count_ham = 0;
                    }
                    if ($count_spam < 0) {
                        $count_spam = 0;
                    }

                    // Now let's see if we have to update or delete the token
                    if ($count_ham != 0 or $count_spam != 0) {
                        $this->update_token($token, [ SpamFilter::KEY_COUNT_HAM => $count_ham,
                            SpamFilter::KEY_COUNT_SPAM => $count_spam ]);
                    } else {
                        $this->delete_token($token);
                    }
                } else {
                    // We don't have the token. If we unlearn a text, we can't delete it as we don't
                    // have it anyway, so just do something if we learn a text
                    if ($action === SpamFilter::LEARN) {
                        if ($category === SpamFilter::HAM) {
                            $this->add_token($token, [ SpamFilter::KEY_COUNT_HAM => $count,
                                SpamFilter::KEY_COUNT_SPAM => 0 ]);
                        } elseif ($category === SpamFilter::SPAM) {
                            $this->add_token($token, [ SpamFilter::KEY_COUNT_HAM => 0,
                                SpamFilter::KEY_COUNT_SPAM => $count ]);
                        }
                    }
                }
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        }catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }
}
