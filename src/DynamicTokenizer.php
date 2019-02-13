<?php

namespace Billitech\Sly;

class DynamicTokenizer extends Tokenizer {

    protected $lastTagPos;
    protected $lastCommentPos;
    protected $lastPrintPos;
    protected $printOpen;
    protected $printClose;
    protected $tagOpen;
    protected $tagClose;
    protected $commentOpen;
    protected $commentClose;
    protected $printOpenLen;
    protected $printCloseLen;
    protected $tagOpenLen;
    protected $tagCloseLen;
    protected $commentOpenLen;
    protected $commentCloseLen;
    protected $tokens = [];
    protected $line = 1;
    protected $file;
    protected $sourse;
    protected $oParents = 0;
    protected $tokensCount = - 1;
    
    function __construct(Definitions $definitions, Array $options = []) {
        parent::__construct($definitions);
        
        $this->setOptions(array_merge(['tag_open' => '{%', 'tag_close' => '%}', 'print_open' => '{{', 'print_close' => '}}', 'comment_open' => '{#', 'comment_close' => '#}'], $options));
    }
    
    public function setOptions(Array $options) {
        foreach($options as $optionKey => $optionValue) {
            switch($optionKey) {
                case 'tag_open' :
                $this->tagOpen = $optionValue;
                $this->tagOpenLen = strlen($optionValue);
                break;
                case 'tag_close' :
                $this->tagClose = $optionValue;
                $this->tagCloseLen = strlen($optionValue);
                break;
                case 'print_open' :
                $this->printOpen = $optionValue;
                $this->printOpenLen = strlen($optionValue);
                break;
                case 'print_close' :
                $this->printClose = $optionValue;
                $this->printCloseLen = strlen($optionValue);
                break;
                case 'comment_open' :
                $this->commentOpen = $optionValue;
                $this->commentOpenLen = strlen($optionValue);
                break;
                case 'comment_close' :
                $this->commentClose = $optionValue;
                $this->commentCloseLen = strlen($optionValue);
                break;
            }
        }
    }
    
    public function getTagOpen() {
        return $this->tagOpen;
    }
    
    public function getTagClose() {
        return $this->tagClose;
    }
    
    public function getPrintOpen() {
        return $this->printOpen;
    }
    
    public function getPrintClose() {
        return $this->printClose;
    }
    
    public function getCommentOpen() {
        return $this->commentOpen;
    }
    
    public function getCommentClose() {
        return $this->commentClose;
    } 
    
    public function tokenize($source, $file = null) {
        $this->source = $source;
        $this->file = $file;
        $this->tokens = [];
        $this->line = 1;
        $this->oParents = 0;
        $this->tokensCount = -1;
        $lastTagPos = null;
        $lastCommentPos = null;
        $lastPrintPos = null;       
        $this->tokenizeSource();
        return new TokenStream($this->tokens, $this->file);
    }
    
    public function tokenizeSource() {
        $this->addToken('start', Token::T_START, Token::T_START);
        $source = $this->source;
        $start = 0;
        $offset = 0;
        while ($source != '') {
            $tagPos = $this->getTagPos($source, $start, $offset);
            $printPos = $this->getPrintPos($source, $start, $offset);
            $commentPos = $this->getCommentPos($source, $start, $offset);

            if ($printPos !== false && ($tagPos === false || $printPos <= $tagPos) && ($commentPos === false || ($printPos != $commentPos && $printPos < $commentPos))) {
                list($source, $start, $offset) = $this->tokenizePrint($source, $printPos);
            } elseif ($tagPos !== false && ($commentPos === false || $tagPos < $commentPos)) {
                list($source, $start, $offset) = $this->tokenizeTag($source, $tagPos);
            } elseif ($commentPos !== false) {
                list($source, $start, $offset) = $this->removeComment($source, $commentPos);
            } else {
                $this->addToken($this->setLine($source), Token::T_TEXT, Token::T_TEXT);
                break;
            }
        }
        
        $this->addToken('end', Token::T_TEND, Token::T_TEND);
    }

    public function getTagPos($source, $start, $offset) {
        if ($this->lastTagPos === false) {
            return false;
        }

        if ($this->lastTagPos > $start) {
            return $this->lastTagPos = $this->lastTagPos - $start;
        }

        return $this->lastTagPos = strpos($source, $this->tagOpen, $offset);
    }

    public function getPrintPos($source, $start, $offset) {
        if ($this->lastPrintPos === false) {
            return false;
        }

        if ($this->lastPrintPos > $start) {
            return $this->lastPrintPos = $this->lastPrintPos - $start;
        }

        return $this->lastPrintPos = strpos($source, $this->printOpen, $offset);
    }

    public function getCommentPos($source, $start, $offset) {
        if ($this->lastCommentPos === false) {
            return false;
        }

        if ($this->lastCommentPos > $start) {
            return $this->lastCommentPos = $this->lastCommentPos - $start;
        }

        return $this->lastCommentPos = strpos($source, $this->commentOpen, $offset);
    }

    public function removeComment($source, $tagPos) {
        if ($this->isValidOpener($source, $tagPos) === false) {
            return [$source, $tagPos + $this->commentOpenLen, $tagPos + $this->commentOpenLen];
        }

        $this->addToken($this->setLine(substr($source, 0, $tagPos)), Token::T_TEXT, Token::T_TEXT);
        if (($end = strpos($source, $this->commentClose, $tagPos + $this->commentCloseLen)) === false) {
            $this->error("Unexpected end of template without ending the comment tag");
        }

        $this->setLine(substr($source, $tagPos, $end + $this->commentCloseLen));
        return [substr($source, $end + $this->commentCloseLen), $end + $this->commentCloseLen, 0];
    }

    public function tokenizePrint($sourse, $pos) {
        if ($this->isValidOpener($sourse, $pos) === false) {
            return [$sourse, $pos + $this->printOpenLen, $pos + $this->printOpenLen];
        }

        $this->addToken($this->setLine(substr($sourse, 0, $pos)), Token::T_TEXT, Token::T_TEXT);
        $this->addToken('print_open', Token::T_PRINT_OPEN, Token::T_PRINT);
        $sourseLen = strlen($sourse);
        $endPos = null;
        for ($i = $pos + $this->printOpenLen; $i < $sourseLen; $i++) {
            if($sourse[$i] == $this->printClose[0] && $this->oParents == 0 && substr($sourse, $i, $this->printCloseLen) == $this->printClose) {
                $endPos = $i;
                break;
            }
            
            $this->setToken($sourse, $i, Token::T_PRINT);
        }

        if ($endPos === null) {
            $this->error('Unexpected end of template without closing the print tag');
        }

        $this->addToken('print_close', Token::T_PRINT_CLOSE, Token::T_PRINT);
        return [substr($sourse, $endPos + $this->printCloseLen), $endPos + $this->printCloseLen, 0];
    }

    public function tokenizeTag($sourse, $tagPos) {
        if ($this->isValidOpener($sourse, $tagPos) === false) {
            return [$sourse, $tagPos + $this->tagOpenLen, $tagPos + $this->tagOpenLen];
        }

        $i = $tagPos + $this->tagOpenLen;
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
        $i++;
        $endPos = null;
        $sourseLen = strlen($sourse);
        for (; $i < $sourseLen; $i++) {
            if($sourse[$i] == $this->tagClose[0] && $this->oParents == 0 && substr($sourse, $i, $this->tagCloseLen) == $this->tagClose) {
                $endPos = $i;
                break;
            }
            
            $this->setToken($sourse, $i, [$tagToken, $value]);
        }

        if ($endPos === null) {
            $this->error("Unexpected end of template without closing the '{$value}' tag");
        }

        $this->addToken('tag_close', Token::T_TAG_CLOSE, [$tagToken, $value]);
        return [substr($sourse, $endPos + $this->tagCloseLen), $endPos + $this->tagCloseLen, 0];
    }
}