<?php
namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * @author      Rafael Kassner <kassner@gmail.com>
 * @author      Oleg Khussainov <getmequick@gmail.com>
 */
class UnixTimestamp extends FunctionNode
{
    public $date;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return sprintf(
            'UNIX_TIMESTAMP(%s)',
            $this->date ? $sqlWalker->walkArithmeticPrimary($this->date) : ''
        );
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        if (!$parser->getLexer()->isNextToken(Lexer::T_CLOSE_PARENTHESIS)) {
            $this->date = $parser->ArithmeticPrimary();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}