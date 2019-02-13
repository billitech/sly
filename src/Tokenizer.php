<?php

namespace Billitech\Sly;

use Billitech\Sly\Exceptions\SyntaxError;

class Tokenizer implements TokenizerInterface {

    protected $definitions;
    protected $tokens = [];
    protected $line = 1;
    protected $file;
    protected $source;
    protected $oParents = 0;
    protected $tokensCount = - 1;

    function __construct(Definitions $definitions) {
        $this->definitions = $definitions;
    }

    public function error($message) {
        throw new SyntaxError("{$message} at line {$this->line} in file '{$this->file}'.");
    }

    public function addToken($value, $type, $parent) {
        if (($value == "\n" || $value == '') && $parent == Token::T_TEXT) {
            return;
        }

        $this->tokens[] = new Token($value, $type, $parent, $this->line);
        $this->tokensCount++;
    }
    
    public function tokenize($source, $file = null) {
        $this->source = $source;
        $this->file = $file;
        $this->tokens = [];
        $this->line = 1;
        $this->oParents = 0;
        $this->tokensCount = -1;
        $this->tokenizeSource();
        return new TokenStream($this->tokens, $this->file);
    }

    protected function tokenizeSource() {
        $this->addToken('start', Token::T_START, Token::T_START);
        $sourse = $this->source;
        $start = 0;
        while ($sourse != '') {
            $pos = strpos($sourse, '@', $start);
            if($pos !== false && isset($sourse[$pos + 1])) {
                 if($sourse[$pos + 1] == '(') {
                     list($sourse, $start) = $this->tokenizePrint($sourse, $pos);
                 } elseif($sourse[$pos + 1] == '*') {
                     list($sourse, $start) = $this->removeComment($sourse, $pos);
                 } else {
                     list($sourse, $start) = $this->tokenizeTag($sourse, $pos);
                 }
             }  else {
                $this->addToken($this->setLine($sourse), Token::T_TEXT, Token::T_TEXT);
                break;
            }
        }
                
        $this->addToken('end', Token::T_TEND, Token::T_TEND);
    }

    public function removeComment($sourse, $pos) {
        if ($this->isValidOpener($sourse, $pos) === false) {
            return [$sourse, $pos + 2];
        }

        $this->addToken($this->setLine(substr($sourse, 0, $pos)), Token::T_TEXT, Token::T_TEXT);        
        if(!isset($sourse[$pos + 2]) || $sourse[$pos + 2] != '-') {
            return $this->removeSingleLineComment($sourse, $pos);
        }

        if (($end = strpos($sourse, "-*", $pos + 3)) === false) {
            $this->error('Unexpected end of template without ending the comment tag');
        }

        $this->setLine(substr($sourse, $pos, $end + 1));
        return [substr($sourse, $end + 2), 0];
    }
    
    
    public function removeSingleLineComment($sourse, $pos) {
        if (($end = strpos($sourse, "\n", $pos + 3)) === false) {
            return ['', 0];
        }

        $this->line++;
        return [substr($sourse, $end + 1), 0];
    }

    public function tokenizePrint($sourse, $pos) {
        if ($this->isValidOpener($sourse, $pos) === false) {
            return [$sourse, $pos + 2];
        }

        $this->addToken($this->setLine(substr($sourse, 0, $pos)), Token::T_TEXT, Token::T_TEXT);
        $this->addToken('print_open', Token::T_PRINT_OPEN, Token::T_PRINT);
        $sourseLen = strlen($sourse);
        $endPos = null;
        for ($i = $pos + 2; $i < $sourseLen; $i++) {
            if($sourse[$i] == ')' && $this->oParents === 0) {
                $endPos = $i;
                break;
            }
            
            $this->setToken($sourse, $i, Token::T_PRINT);
        }

        if ($endPos === null) {
            $this->error('Unexpected end of template without closing the print tag');
        }

        $this->addToken('print_close', Token::T_PRINT_CLOSE, Token::T_PRINT);
        return [substr($sourse, $endPos + 1), 0];
    }

    public function tokenizeTag($sourse, $tagPos) {
        if ($this->isValidOpener($sourse, $tagPos) === false) {
            return [$sourse, $tagPos + 1];
        }

        $i = $tagPos + 1;
        while (isset($sourse[$i]) && ($sourse[$i] == ' ' || $sourse[$i] == "\n")) {
            $i++;
        }

        $value = '';
        while (isset($sourse[$i]) && (($sourse[$i] >= 'a' && $sourse[$i] <= 'z') || ($sourse[$i] >= 'A' && $sourse[$i] <= 'Z') || $sourse[$i] == '_')) {
            $value .= $sourse[$i++];
        }

        $this->addToken($this->setLine(substr($sourse, 0, $tagPos)), Token::T_TEXT, Token::T_TEXT);
        if (($tagToken = $this->definitions->getTag($value)) === null) {
            $this->error("invalid tag '{$value}'");
        }

        $this->addToken('tag_open', Token::T_TAG_OPEN, [$tagToken, $value]);
        $this->addToken($value, Token::T_TAG, [$tagToken, $value]);
        if ($value == 'macro') {
            $this->oParents--;
            $i--;
        } elseif (!isset($sourse[$i]) || $sourse[$i] != '(') {
            $this->addToken('tag_close', Token::T_TAG_CLOSE, [$tagToken, $value]);
            return [substr($sourse, $i), 0];
        }

        $i++;
        $endPos = null;
        $sourseLen = strlen($sourse);
        for (; $i < $sourseLen; $i++) {
            if($sourse[$i] == ')' && $this->oParents === 0) {
                if ($value == 'macro') {
                    $this->addToken(')', Token::T_CPARENT, [$tagToken, $value]);
                }
                $endPos = $i;
                break;
            }
            
            $this->setToken($sourse, $i, [$tagToken, $value]);
        }

        if ($endPos === null) {
            $this->error("Unexpected end of template without closing the '{$value}' tag");
        }

        $this->addToken('tag_close', Token::T_TAG_CLOSE, [$tagToken, $value]);
        return [substr($sourse, $endPos + 1), 0];
    }

    public function setToken($sourse, &$i, $group) {        
        switch ($sourse[$i]) {
            case ' ':
                break;
            case '"':
            case "'":
                $end = $sourse[$i];
                $value = '';
                while (isset($sourse[$i + 1]) && ($sourse[++$i] != $end || !$this->isValidQuoteEnd($sourse, $i))) {
                    if ($sourse[$i] == "\n") {
                        $this->line++;
                    }
                    $value .= $sourse[$i];
                }

                $this->addToken($value, Token::T_STRING, $group);
                break;
            case "\n":
                $this->line++;
                break;
            case '(':
                $this->oParents++;
                $this->addToken('(', Token::T_OPARENT, $group);
                break;
            case ')':
                $this->addToken(')', Token::T_CPARENT, $group);
                $this->oParents--;
                break;
            default:
                if ($sourse[$i] >= '0' && $sourse[$i] <= '9') {
                    $value = $sourse[$i];
                    $hasDot = false;
                    while (isset($sourse[++$i]) && (($sourse[$i] >= '0' && $sourse[$i] <= '9') || ($hasDot == false && $sourse[$i] == '.' && isset($sourse[$i + 1]) && ($sourse[$i + 1] >= '0' && $sourse[$i + 1] <= '9') && ($hasDot = true)))) {
                        $value .= $sourse[$i];
                    }
                    $i--;
                    $this->addToken($value, Token::T_NUMERIC, $group);
                    break;
                }

                if (($sourse[$i] >= 'a' && $sourse[$i] <= 'z') || ($sourse[$i] >= 'A' && $sourse[$i] <= 'Z') || $sourse[$i] == '_') {
                    $value = $sourse[$i];
                    while (isset($sourse[++$i]) && (($sourse[$i] >= 'a' && $sourse[$i] <= 'z') || ($sourse[$i] >= 'A' && $sourse[$i] <= 'Z') || $sourse[$i] == '_' || ($sourse[$i] >= '0' && $sourse[$i] <= '9'))) {
                        $value .= $sourse[$i];
                    }
                    $i--;
                    $type = Token::T_VARIABLE;
                    if ($this->tokens[$this->tokensCount]->type() === Token::T_FILTER_PIPE) {
                        $type = Token::T_FILTER;
                    } elseif (($nextTest = $this->nextIs($sourse, $i, ['(', '\\', '::'])) && $nextTest[0] === true) {
                        $type = Token::T_FUNCTION;
                    } elseif ($nextTest[1] === true) {
                        $type = Token::T_NAMESPACE;
                    } elseif ($nextTest[2] === true) {
                        $type = Token::T_CLASS;
                    } elseif ($this->definitions->isConstant($value)) {
                        $type = Token::T_CONSTANT;
                    }

                    $this->addToken($value, $type, $group);
                    break;
                }

                $value = substr($sourse, $i, 3);
                $vLen = strlen($value);
                $i += $vLen - 1;
                while (!empty($value) && !$this->definitions->isOperator($value)) {
                    $value = substr($value, 0, --$vLen);
                    $i--;
                }

                if (!empty($value)) {
                    $this->addToken($value, $this->definitions->getOperator($value), $group);
                    break;
                }
                
                $this->error("Unexpected $sourse[$i] ");
        }
    }

    public function isValidOpener(&$sourse, &$pos) {
        if ((isset($sourse[$pos - 1]) && $sourse[$pos - 1] == "\\")) {
            if (!isset($sourse[$pos - 2]) || $sourse[$pos - 2] != "\\") {
                $sourse = substr_replace($sourse, '', $pos - 1, 1);
                $pos -= 1;
                return false;
            }

            $sourse = substr_replace($sourse, '', $pos - 1, 1);
            $pos -= 1;
        }

        return true;
    }

    public function isValidQuoteEnd($sourse, $pos) {
        if ((isset($sourse[$pos - 1]) && $sourse[$pos - 1] === "\\")) {
            if (!isset($sourse[$pos - 2]) || $sourse[$pos - 2] !== "\\") {
                return false;
            }
        }

        return true;
    }

    public function setLine($sourse) {
        $this->line += substr_count($sourse, "\n");
        return $sourse;
    }

    public function nextIs($sourse, $i, $needles = []) {
        while (isset($sourse[++$i]) && ($sourse[$i] == ' ' || $sourse[$i] == "\n")) {
        }
        $return = [];
        foreach ($needles as $needle) {
            if (substr($sourse, $i, strlen($needle)) == $needle) {
                $return[] = true;
            } else {
                $return[] = false;
            }
        }
        return $return;
    }

}