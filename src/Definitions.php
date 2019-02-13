<?php

namespace Billitech\Sly;

class Definitions {
    
    /**
     * The array of acceptable sly constants.
     *
     * @var array
     */
    protected $constants = [
        'null' => 1,
        'false' => 1,
        'true' => 1
    ];

    /**
     * The tags array.
     *
     * @var array
     */
    protected $tags = [
            'autoescape' => Token::T_AUTOESCAPE,
            'endautoescape' => Token::T_EAUTOESCAPE,
            'block' => Token::T_BLOCK,
            'endblock' => Token::T_EBLOCK,
            'parent' => Token::T_PARENT,
            'else' => Token::T_ELSE,
            'elseif' => Token::T_ELSEIF,
            'extends' => Token::T_EXTENDS,
            'for' => Token::T_FOR,
            'elsefor' => Token::T_ELSEFOR,
            'endfor' => Token::T_EFOR,
            'while' => Token::T_WHILE,
            'endwhile' => Token::T_EWHILE,
            'if' => Token::T_IF,
            'endif' => Token::T_EIF,
            'include' => Token::T_INCLUDE,
            'load' => Token::T_LOAD,
            'set' => Token::T_SET,
            'endset' => Token::T_ESET,
            'switch' => Token::T_SWITCH,
            'endswitch' => Token::T_ESWITCH,
            'case' => Token::T_CASE,
            'break' => Token::T_BREAK,
            'continue' => Token::T_CONTINUE,
            'default' => Token::T_DEFAULT,
            'raw' => Token::T_RAW,
            'macro' => Token::T_MACRO,
            'endmacro' => Token::T_EMACRO,
            'import' => Token::T_IMPORT,
            'end' => Token::T_END,
            'do' => Token::T_DO,
            'e' => Token::T_E,
            'filter' => Token::T_TFILTER,
            'endfilter' => Token::T_ETFILTER,
            'spaceless' => Token::T_SPACELESS,
            'endspaceless' => Token::T_ESPACELESS,
                ];
    
    /**
     * The array of maths operators.
     *
     * @var array
     */            
    protected $mathsOperators = [
            '+' => Token::T_PLUS,
            '-' => Token::T_MINUS,
            '/' => Token::T_DIV,
            '*' => Token::T_TIMES,
            '%' => Token::T_MOD,
            '**' => Token::T_PAW
                ];
    
    /**
     * The array of comparison operators.
     *
     * @var array
     */                
    protected $comparisonOperators = [
            '<' => Token::T_LT,
            '>' => Token::T_GT,
            '<=' => Token::T_LE,
            '===' => Token::T_IDENTICAL,
            '==' => Token::T_EQ,
            '>=' => Token::T_GE,
            '!==' => Token::T_NE_IDENTICAL,
            '!=' => Token::T_NE,
            '<>' => Token::T_NE
                ];
    
    /**
     * The array of logic operators.
     *
     * @var array
     */
    protected $logicOperators = [
            '&&' => Token::T_AND,
            '||' => Token::T_OR,
            'and' => Token::T_AND,
            'or' => Token::T_OR,
            'xor' => Token::T_XOR
                ];
    
    /**
     * The array of array operators.
     *
     * @var array
     */
    protected $arrayOperators = [
            '[' => Token::T_OBRACKETS,
            ']' => Token::T_CBRACKETS,
            '=>' => Token::T_ARRAY_OPERATOR
                ];
    
    /**
     * The array of assign operators.
     *
     * @var array
     */
    protected $assignOperators = [
            '=' => Token::T_ASSIGN,
            '+=' => Token::T_ADD_ASSIGN,
            '-=' => Token::T_SUB_ASSIGN,
            '*=' => Token::T_MUNTI_ASSIGN,
            '/=' => Token::T_DIV_ASSIGN,
            '.=' => Token::T_CONCAT_ASSIGN
                ];
        
    protected $incrDecrOperators = [       
        '++' => Token::T_PLUS1,
        '--' => Token::T_MINUS1,
    ];
    
    /**
     * The array of class operators.
     *
     * @var array
     */ 
    protected $classOperators = [
            '->' => Token::T_OBJ,
            '::' => Token::T_CLASS_OPERATOR,
            '\\' => Token::T_NSSEPERATOR
                ];
    
    /**
     * The array of ternary operators.
     *
     * @var array
     */
    protected $ternaryOperators = [
            '?:' => Token::T_TERNARY_OPERATOR,
            '?' => Token::T_QUESTION
                ];
    
    /**
     * The array all the operators.
     *
     * @var array
     */
    protected $operators = [
            '~' => Token::T_CONCATE,
            ';' => Token::T_SCOLON,
            ':' => Token::T_COLON,
            '!' => Token::T_NOT,
            '(' => Token::T_OPARENT,
            ')' => Token::T_CPARENT,
            ',' => Token::T_COMMA,
            '.' => Token::T_DOT,
            '|' => Token::T_FILTER_PIPE,
            '..' => Token::T_DOTDOT,
            'is' => Token::T_TEST_OPERATOR,
            'in' => Token::T_IN
                ];
    
    /**
     * The array of token parsers.
     *
     * @var array
     */
    protected $tokenParsers = [
            Token::T_TAG_OPEN => 'parseTag',
            Token::T_PRINT_OPEN => 'parsePrint',
            Token::T_TEXT => 'parseText'
        ];
    
    /**
     * The array of tag parsers.
     *
     * @var array
     */
    protected $tagParsers = [
        'autoescape' => 'parseAutoescapeTag',
            'block' => 'parseBlockTag',
            'parent' => 'parseParentTag',
            'extends' => 'parseExtendsTag',
            'for' => 'parseForTag',
            'while' => 'parseWhileTag',
            'if' => 'parseIfTag',
            'include' => 'parseIncludeTag',
            'load' => 'parseLoadTag',
            'set' => 'parseSetTag',
            'switch' => 'parseSwitchTag',
            'break' => 'parseBreakTag',
            'continue' => 'parseContinueTag',
            'raw' => 'parseRawTag',
            'macro' => 'parseMacroTag',
            'import' => 'parseImportTag',
            'do' => 'parseDoTag',
            'e' => 'parseETag',
            'filter' => 'parseFilterTag',
            'spaceless' => 'parseSpacelessTag'
        ];
    
    /**
     * The array of expression parsers.
     *
     * @var array
     */
    protected $exprParsers = [
            Token::T_VARIABLE => 'parseVariable',
            Token::T_STRING => 'parseString',
            Token::T_NUMERIC => 'parseNumber',
            Token::T_OPARENT => 'parseOparent',
            Token::T_FUNCTION => 'parseFunction',
            Token::T_OBRACKETS => 'parseBracket',
            Token::T_NOT => 'parseNot',
            Token::T_NAMESPACE => 'parseNamespcace',
            Token::T_CLASS => 'parseClass',
            Token::T_NSSEPERATOR => 'parseNsSeperator',
            Token::T_PLUS1 => 'parseIncrDecrExpr',
            Token::T_MINUS1 => 'parseIncrDecrExpr',
            Token::T_CONSTANT => 'parseConstant'
        ];
    
    /**
     * Class constructor.
     */
    public function __construct() {
        $this->operators = array_merge($this->operators, $this->comparisonOperators, $this->mathsOperators, $this->arrayOperators, $this->logicOperators, $this->assignOperators, $this->incrDecrOperators, $this->classOperators, $this->ternaryOperators);
    }
    
    /**
     * adds a given tag to the tags array and add the given parser for the tag to the tagparsers array if given.
     *
     * @param string $tag
     * @param mixed $parser
     * @return $this
     */
    public function addTag($tag, $parser = null) {
        if(!isset($this->tags[$tag])) {
            $this->tags[$tag] = Token::T_CUSTOM_TAG;
        }
        if($parser != null) {
            $this->tagParsers[$tag] = $parser;
        }
        
        return $this;
    }
    
    /**
     * Checks if the given tag exists in the tags array.
     *
     * @param string $tag
     * @return bool
     */
    public function hasTag($tag) {
        return isset($this->tags[$tag]);
    }
    
    /**
     * Gets the given tag token type from the tags array or return null if not set.
     *
     * @param string $tag
     * @return int|null
     */
    public function getTag($tag) {
        return isset($this->tags[$tag]) ? $this->tags[$tag] : null;
    }
    
    /**
     * Gets the tags array.
     *
     * @return array
     */
    public function getTags() {
        return $this->tags;
    }
    
    /**
     * Checks if the given operator exists in the operators array.
     *
     * @param string $operator
     * @return bool
     */
    public function isOperator($operator) {
        return isset($this->operators[$operator]);
    }
    
    /**
     * Gets the token type of the given operator or return null if not set.
     *
     * @param string $operator
     * @return int|null
     */
    public function getOperator($operator) {
        return isset($this->operators[$operator]) ? $this->operators[$operator] : null;
    }
    
    /**
     * Gets the operators array.
     *

     * @return array
     */
    public function getOperators() {
        return $this->operators;
    }
    
    /**
     * Checks if the given operator exists in the mathsOperators array.
     *
     * @param string $operator
     * @return bool
     */
    public function isMathsOperator($operator) {
        return isset($this->mathsOperators[$operator]);
    }
    
    /**
     * Gets the mathsOperators array.
     *

     * @return array
     */
    public function getMathsOperators() {
        return $this->mathsOperators;
    }
    
    /**
     * Checks if the given operator exists in the comparisonOperators array.
     *
     * @param string $operator
     * @return bool
     */
    public function isComparisonOperator($operator) {
        return isset($this->comparisonOperators[$operator]);
    }
    
    /**
     * Gets the comparisonOperators array.
     *

     * @return array
     */
    public function getComparisonOperators() {
        return $this->comparisonOperators;
    }
    
    /**
     * Checks if the given operator exists in the logicOperators array.
     *
     * @param string $operator
     * @return bool
     */
    public function isLogicOperator($operator) {
        return isset($this->logicOperators[$operator]);
    }
    
    /**
     * Gets the logicOperators array.
     *

     * @return array
     */
    public function getLogicOperators() {
        return $this->logicOperators;
    }
    
    /**
     * Checks if the given operator exists in the arrayOperators array.
     *
     * @param string $operator
     * @return bool
     */
    public function isArrayOperator($operator) {
        return isset($this->arrayOperators[$operator]);
    }
    
    /**
     * Gets the arrayOperators array.
     *

     * @return array
     */
    public function getArrayOperators() {
        return $this->arrayOperators;
    }
    
    /**
     * Checks if the given operator exists in the assignOperators array.
     *
     * @param string $operator
     * @return bool
     */ 
    public function isAssignOperator($operator) {
        return isset($this->assignOperators[$operator]);
    }
    
    /**
     * Gets the assignOperators array.
     *

     * @return array
     */
    public function getAssignOperators() {
        return $this->assignOperators;
    }
    
    /**
     * Checks if the given operator exists in the incrDecrOperators array.
     *
     * @param string $operator
     * @return bool
     */ 
    public function isIncrDecrOperator($operator) {
        return isset($this->incrDecrOperators[$operator]);
    }
    
    /**
     * Gets the incrDecrOperators array.
     *

     * @return array
     */
    public function getIncrDecrOperators() {
        return $this->incrDecrOperators;
    }
    
    /**
     * Checks if the given operator exists in the classOperators array.
     *
     * @param string $operator
     * @return bool
     */ 
    public function isClassOperator($operator) {
        return isset($this->classOperators[$operator]);
    }
    
    /**
     * Gets the classOperators array.
     *

     * @return array
     */
    public function getClassOperators() {
        return $this->classOperators;
    }
    
    /**
     * Checks if the given operator exists in the ternartOperators array.
     *
     * @param string $operator
     * @return bool
     */ 
    public function isTernaryOperator($operator) {
        return isset($this->ternaryOperators[$operator]);
    }
    
    /**
     * Gets the ternaryOperators array.
     *

     * @return array
     */
    public function getTernaryOperators() {
        return $this->ternaryOperators;
    }
    
    /**
     * Checks if the given value exists in the constants array.
     *
     * @param string $value
     * @return bool
     */ 
    public function isConstant($value) {
        return isset($this->constants[$value]);
    }
    
    /**
     * Gets an array of all the acceptable constants.
     *

     * @return array
     */
    public function getConstants() {
        return array_keys($this->constants);
    }
    
    /**
     * Checks if a token parser is set for the given token type.
     *
     * @param string $tokenType
     * @return bool
     */
    public function hasTokenParser($tokenType) {
        return isset($this->tokenParsers[$tokenType]);
    }
    
    /**
     * Gets the token parser of the given token type or return null if not set.
     *
     * @param string $tokenType
     * @return mixed
     */
    public function getTokenParser($tokenType) {
        return isset($this->tokenParsers[$tokenType]) ? $this->tokenParsers[$tokenType] : null;
    }
    
    /**
     * Gets the tokenParsers array.
     *

     * @return array
     */
    public function getTokenParsers() {
        return $this->tokenParsers;
    }
    
    /**
     * Checks if a tag parser is set for the given tag name.
     *
     * @param string $tag
     * @return bool
     */
    public function hasTagParser($tag) {
        return isset($this->tagParsers[$tag]);
    }
    
    /**
     * Gets the tag parser of the given tag or return null if not set.
     *
     * @param string $tag
     * @return mixed
     */
    public function getTagParser($tag) {
        return isset($this->tagParsers[$tag]) ? $this->tagParsers[$tag] : null;
    }
    
    /**
     * Gets the tagParsers array.
     *

     * @return array
     */
    public function getTagParsers() {
        return $this->tagParsers;
    }
    
    /**
     * Checks if an expression parser is set for the given token type.
     *
     * @param string $tokenType
     * @return bool
     */
    public function hasExprParser($tokenType) {
        return isset($this->exprParsers[$tokenType]);
    }
    
    /**
     * Gets the expression parser of the given token type or return null if not set.
     *
     * @param string $tokenType
     * @return mixed
     */
    public function getExprParser($tokenType) {
        return isset($this->exprParsers[$tokenType]) ? $this->exprParsers[$tokenType] : null;
    }
    
    /**
     * Gets the exprParsers array.
     *

     * @return array
     */
    public function getExprParsers() {
        return $this->exprParsers;
    }
    
}