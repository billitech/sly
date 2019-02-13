<?php

namespace Billitech\Sly;

use ArrayAccess;
use Billitech\Sly\Exceptions\SyntaxError;

class TokenStream implements ArrayAccess {

    /**
     * Array of tokens.
     *
     * @var array
     */
    protected $tokens;

    /**
     * Total number of tokens.
     *
     * @var int
     */
    protected $total;

    /**
     * The current token position.
     *
     * @var int
     */
    protected $current = 0;

    /**
     * The name of the filename which tokens are associated with.
     *
     * @var string
     */
    protected $filename;

    /**
     * Class onstructor
     *
     * @param array  $tokens
     * @param string $filename
     */
    public function __construct(array $tokens, $filename = null) {
        $this->tokens = $tokens;
        $this->total = count($tokens);
        $this->filename = $filename;
    }

    /**
     * Throw a sintax error.
     *
     * @param string $message
     * @param int $position
     * @throws \Billitech\Sly\Exceptions\SyntaxError
     */
    public function error($message, $position = null) {
        if($position === null) {
            $position = $this->current;
        }
        $token = $this->get($position);
        throw new SyntaxError("{$message} at line '{$token->line()}' in '{$this->filename}'.");
    }

    /**
     * Throw a sintax error.
     *
     * @param \Billitech\Sly\Token $token
     * @param int $type
     * @param string $value
     * @throws \Billitech\Sly\Exceptions\SyntaxError
     */
    public function unexpectedToken(Token $token, $type = null, $value = null) {
        $expected = '';

        if (isset($type)) {
            $expected .= " expected token type '{$this->transType($type)}'";
            if (!isset($value)) {
                $value = $this->guessTypeValue($type);
            }
        }

        if (isset($value)) {

            $expected .= isset($type) ? ' with' : ' expected token with';
            $expected .= " value '{$value}'";
        }

        throw new SyntaxError("Unexpected token type '{$token->name()}' with value '{$token->value()}'{$expected} in '{$this->filename}' at line '{$token->line()}'.");
    }

    /**
     * Get the given type name.
     *
     * @param int $type
     * @throws string
     */
    public function transType($type) {
        return Translator::getTypeName($type);
    }

    /**
     * Guess and get the posible type value.
     *
     * @param int $type
     * @throws string
     */
    public function guessTypeValue($type) {
        return Translator::guessTypeValue($type);
    }

    /**
     * Returns a string representation of the token stream.
     *
     * @return string
     */
    public function __toString() {
        return implode("\n", $this->tokens);
    }

    /**
     *  Merge a given tokens array to the present tokens.
     *
     * @param array $tokens
     * @return void
     */
    public function injectTokens(array $tokens) {
        $this->tokens = array_merge(array_slice($this->tokens, 0, $this->current), $tokens, array_slice($this->tokens, $this->current));
        $this->total = count($this->tokens);
    }

    /**
     * Sets the pointer to the next token.
     *
     * @return $this
     */
    public function incr($number = 1) {
        if (!isset($this->tokens[$this->current += $number])) {
            $position = $this->current -= $number;
            while(isset($this->tokens[$position + 1])) {
                $position++;
            } 
            $this->error('Unexpected end of template', $this->current - $position);
        }
        return $this;
    }

    /**
     * Sets the pointer to the next token and returns the token.
     *
     * @return \Billitech\Sly\Token
     */
    public function next() {
        if (!isset($this->tokens[++$this->current])) {
            $this->error('Unexpected end of template', --$this->current);
        }

        return $this->tokens[$this->current];
    }

    /**
     * Sets the pointer to the next one if it matches the given condition or throw error.
     *
     * @param int $type
     * @param string $value
     * @param int $parent
     * @param string $parentVal
     * @return \Billitech\Sly\Token
     */
    public function nextIf($type, $value = null, $parent = null, $parentVal = null) {
        $this->nextShouldBe($type, $value, $parent, $parentVal);
        return $this->tokens[++$this->current];
    }

    /**
     * Tests the next token and returns it or throws a syntax error.
     *
     * @param int $type
     * @param string $value
     * @param int $parent
     * @param string $parentVal
     * @return void
     */
    public function nextShouldBe($type, $value = null, $parent = null, $parentVal = null) {

        if (!$this->nextIs($type, $value, $parent, $parentVal)) {
            $this->unexpectedToken($this->tokens[$this->current + 1], $type, $value);
        }
    }

    /**
     * Tests next token by the given parameters and returns it or throws a syntax error.
     *
     * @param int $type
     * @param string $value
     * @param int $parent
     * @param string $parentVal
     * @return bool
     */
    public function nextIs($type, $value = null, $parent = null, $parentVal = null) {
        if (!isset($this->tokens[$this->current + 1])) {
            return false;
        }
        return $this->tokens[$this->current + 1]->is($type, $value, $parent, $parentVal);
    }

    /**
     * Chech if  next token is set.
     *
     * @param int $number
     * @return bool
     */
    public function nextIsSet($number = 1) {
        return isset($this->tokens[$this->current + $number]);
    }

    /**
     * Chech if  prev token is set.
     *
     * @param int $number
     * @return bool
     */
    public function prevIsSet($number = 1) {
        return isset($this->tokens[$this->current - $number]);
    }

    /**
     * Sets the pointer to the previous token.
     *
     * @return $this
     */
    public function decr($number = 1) {
        if (!isset($this->tokens[$this->current -= $number])) {
            $position = $this->current += $number;
            while(isset($this->tokens[$position - 1])) {
                $position--;
            }
    
            $this->error('Unexpected start of template', $position);
        }
        return $this;
    }

    /**
     * Sets the pointer to the previous token and returns it.
     *
     * @return \Billitech\Sly\Token
     */
    public function prev() {
        if (!isset($this->tokens[--$this->current])) {
            $this->error('Unexpected start of template', ++$this->current);
        }

        return $this->tokens[$this->current];
    }

    /**
     * Sets the pointer to the previous one if it matches the given parameters or throw error.
     *
     * @param int $type
     * @param string $value
     * @param int $parent
     * @param string $parentVal
     * @return \Billitech\Sly\Token
     */
    public function prevIf($type, $value = null, $parent = null, $parentVal = null) {
        $this->prevShouldBe($type, $value, $parent, $parentVal);
        return $this->tokens[--$this->current];
    }

    /**
     * Tests prev token and throws a syntax error if fails.
     *
     * @param int $type
     * @param string $value
     * @param int $parent
     * @param string $parentVal
     * @return void
     */
    public function prevShouldBe($type, $value = null, $parent = null, $parentVal = null) {
        if (!$this->prevIs($type, $value, $parent, $parentVal)) {
            $this->unexpectedToken($this->tokens[$this->current - 1], $type, $value);
        }
    }

    /**
     * Tests prev token by the given parameters.
     *
     * @param int $type
     * @param string $value
     * @param int $parent
     * @param string $parentVal
     * @return bool
     */
    public function prevIs($type, $value = null, $parent = null, $parentVal = null) {
        if (!isset($this->tokens[$this->current - 1])) {
            return false;
        }

        return $this->tokens[$this->current - 1]->is($type, $value, $parent, $parentVal);
    }

    /**
     * Looks at the next given token.
     *
     * @param int $number
     *
     * @return \Billitech\Sly\Token
     */
    public function look($number = 1) {
        if (!isset($this->tokens[$this->current + $number])) {
            $position = $this->current;
            while(isset($this->tokens[$position + 1])) {
                $position++;
            }
            $this->error('Unexpected end of template', $position);
        }

        return $this->tokens[$this->current + $number];
    }

    /**
     * Looks at the previous given token.
     *
     * @param int $number
     *
     * @return \Billitech\Sly\Token
     */
    public function lookback($number = 1) {
        if (!isset($this->tokens[$this->current - $number])) {
            $position = $this->current;
            while(isset($this->tokens[$position - 1])) {
                $position--;
            }
            $this->error('Unexpected start of template', $position);
        }

        return $this->tokens[$this->current - $number];
    }

    /**
     * Get an array of all the tokens.
     *
     *
     * @return array
     */
    public function all() {
        return $this->tokens;
    }

    /**
     * Get a token by the given position.
     *
     * @param int $position
     *
     * @return \Billitech\Sly\Token
     */
    public function get($position = 0) {
        if (!isset($this->tokens[$position])) {
            $position = $this->current;
            while(isset($this->tokens[$position + 1])) {
                $position++;
            }
            $this->error('Unexpected end of template', $position);
        }

        return $this->tokens[$position];
    }

    /**
     * Checks if is the first stream.
     *
     * @return bool
     */
    public function isStart() {
        return $this->tokens[$this->current]->type() === Token::T_START;
    }

    /**
     * Checks if end of stream was reached.
     *
     * @return bool
     */
    public function isEnd() {
        return $this->tokens[$this->current]->type() === Token::T_TEND;
    }

    /**
     * Gets the current token.
     *
     * @return \Billitech\Sly\Token
     */
    public function current() {
        return $this->tokens[$this->current];
    }

    /**
     * Gets the filename associated with this stream.
     *
     * @return string
     */
    public function filename() {
        return $this->filename;
    }

    /**
     * Check if a token exists based on the position.
     *
     * @param int $position
     * @return bool
     */
    public function has($position) {
        return isset($this->tokens[$position]);
    }

    /**
     * Return total number of token present.
     *
     * @return int
     */
    public function count() {
        return $this->total;
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param mixed $offset            
     * @param mixed $value            
     * @return void
     */
    public function offsetSet($offset, $value) {
        
    }

    /**
     * Whether or not an offset exists
     *
     * @param mixed $offset        
     * @return bool
     */
    public function offsetExists($offset) {
        return $this->has($offset);
    }

    /**
     * Unset an offset
     *
     * @param mixed $offset        
     * @return void
     */
    public function offsetUnset($offset) {
        
    }

    /**
     * Returns the value at specified offset
     *
     * @param mixed $offset        
     * @return \Billitech\Sly\Token
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

}