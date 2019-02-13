<?php

namespace Billitech\Sly;

use Billitech\Sly\Exceptions\SyntaxError;

class Parser implements ParserInterface {

    public $breakable = 0;
    public $continueable = 0;
    public $extendable = true;

    /**
     * The definitions instance.
     *
     * @var Definitions
     */
    protected $definitions;
    /**
     * The compiler instance.
     *
     * @var CompilerInterface
     */
    protected $compiler;
    /**
     * The file been parsed.
     *
     * @var string
     */
    protected $file;
    /**
     * The token stream instance.
     *
     * @var TokenStream
     */
    protected $stream;

    function __construct(Definitions $definitions) {
        $this->definitions = $definitions;
    }
    
    public function getStream() {
        return $this->stream;
    }
    
    public function getCompiler() {
        return $this->compiler;
    }
    
    public function getDefinitions() {
        return $this->definitions;
    }

    public function error($message, $line) {
        throw new SyntaxError($message . " at line '{$line}' in '{$this->file}'.");
    }

    public function unexpectedToken(Token $token, $type = null, $value = null) {
        $expected = '';

        if (isset($type)) {
            $expected .= " expected token type '{$this->stream->transType($type)}'";
            if (!isset($value)) {
                $value = $this->stream->guessTypeValue($type);
            }
        }

        if (isset($value)) {

            $expected .= isset($type) ? ' with' : ' expected token with';
            $expected .= " value '{$value}'";
        }

        throw new SyntaxError("Unexpected token type '{$token->name()}' with value '{$token->value()}'{$expected} in '{$this->file}' at line '{$token->line()}'.");
    }
    
    public function parse(TokenStream $stream, CompilerInterface $compiler) {        
        $this->breakable = 0;
        $this->continueable = 0;
        $this->extendable = true;
        $this->stream = $stream;
        $this->file = $stream->filename();
        $this->compiler = $compiler->newInstance($this);
        return $this->parseTokens();
    }

    protected function parseTokens() {
        $code = '';
        while (!$this->stream->incr()->isEnd()) {
            $code .= $this->parseToken($this->stream);
        }

        return $this->compiler->toClass($code);
    }

    public function callParser($parser, TokenStream $stream) {
        if (is_string($parser) && method_exists($this, $parser)) {
            return $this->{$parser}($stream);
        }

        if (is_callable($parser)) {
            return call_user_func_array($parser, [$stream, $this, $this->compiler]);
        }

        $token = $stream->current();
        $this->error("Unable to call the parser for token type '{$token->name()}' with value '{$token->value()}'", $token->line());
    }

    public function parseToken(TokenStream $stream) {
        $token = $stream->current();
        if (($parser = $this->definitions->getTokenParser($token->type())) == null) {
            $this->unexpectedToken($token);
        }
        
        return $this->callParser($parser, $stream);
    }

    public function parseExpr(TokenStream $stream, $ignorOperators = false) {
        $token = $stream->current();
        if (($parser = $this->definitions->getExprParser($token->type())) == null) {
            $this->unexpectedToken($token);
        }
        
        $return = $this->callParser($parser, $stream);
        while ($this->definitions->isOperator($stream->look()->value())) {

            switch ($stream->look()->type()) {
                case Token::T_STRING:
                    return $return;
                case Token::T_VARIABLE:
                    $value = $stream->look()->value();
                    if ($value == 'in') {
                        if ($ignorOperators === 2) {
                            return $return;
                        }
                        $return = $this->parseIn($stream->incr(), $return);
                        continue;
                    }
                    
                    if ($value == 'is') {
                        $return = $this->parseTest($stream->incr(), $return);
                        continue;
                    }
                    
                    if($this->definitions->isLogicOperator($value)) {
                        if ($ignorOperators === true) {
                            return $return;
                        }
                        
                        $return = $this->parseLogicOperator($stream->incr(), $return, $value);
                        continue;
                    }
                    
                    return $return;          
                case Token::T_CONCATE:
                    $return = $this->parseConcate($stream->incr(), $return);
                    break;
                case Token::T_FILTER_PIPE:
                    $return = $this->parseInlineFilter($stream->incr(), $return);
                    break;
                case Token::T_QUESTION:
                case Token::T_TERNARY_OPERATOR:

                    if ($ignorOperators === true) {
                        return $return;
                    }
                    $return = $this->parseTernaryExpr($stream->incr(), $return);
                    break;
                default:
                    $operator = $stream->look()->value();
                    if ($this->definitions->isAssignOperator($operator)) {
                        $return = $this->parseAssignExpr($stream->incr(), $return);
                        continue;
                    }
                    
                    if ($this->definitions->isIncrDecrOperator($operator)) {
                        $return = $this->parseIncrDecrExpr($stream->incr(), $return);
                        continue;
                    }

                    if ($ignorOperators === true) {
                        return $return;
                    }
                                                                        
                    if ($this->definitions->isComparisonOperator($operator)) {
                        $return = $this->parseCompOperator($stream->incr(), $return, $operator);
                        continue;
                    }
                    
                    if($this->definitions->isLogicOperator($operator)) {
                        $return = $this->parseLogicOperator($stream->incr(), $return, $operator);
                        continue;
                    }
                    
                    if($this->definitions->isMathsOperator($operator)) {
                        $return = $this->parseMathsOperator($stream->incr(), $return, $operator);
                        continue;
                    }
                    
                    return $return;
            }
        }

        return $return;
    }

    public function parseTag(TokenStream $stream) {
        $tag = $stream->next()->value();
        if (($parser = $this->definitions->getTagParser($tag)) == null) {
            $this->unexpectedToken($stream->current());
        }

        return $this->callParser($parser, $stream);
    }
   
    public function parseVariable(TokenStream $stream) {             
        $line = $stream->current()->line();
        $attributes[] = [$stream->current()->value(), '[var'];
        
        while(($token = $stream->next()->type()) == Token::T_DOT || $token == Token::T_OBJ || $token == Token::T_OBRACKETS) {
            if ($token == Token::T_OBRACKETS) {
                $attributes[] = [$this->parseExpr($stream->incr()), '['];
                $stream->nextIf(Token::T_CBRACKETS);
            } elseif ($token == Token::T_OBJ && $stream->look()->type() != Token::T_NUMERIC) {
                $attributes[] = [$stream->nextIf(Token::T_VARIABLE)->value(), '->'];
            } elseif(($nextToken = $stream->look()->type()) == Token::T_VARIABLE) {
                $attributes[] = [$stream->next()->value(), '[var'];
            } elseif($nextToken == Token::T_NUMERIC) {
                $attributes[] = [$stream->next()->value(), '['];
            } elseif($nextToken == Token::T_FUNCTION) {
                return $this->parseMethod($stream->incr(), new Variable($attributes, $line, $this->compiler));
            } else {
                $this->unexpectedToken($stream->next());
            }
        }
        
        $stream->decr();
        return new Variable($attributes, $line, $this->compiler);
    }

    public function parseConstant(TokenStream $stream) {
        return $this->compiler->compileConstant($stream->current()->value());
    }

    public function parseNsSeperator(TokenStream $stream) {
        if(($next = $stream->next()->type()) == Token::T_NAMESPACE) {
            return $this->parseNamespace($stream, true);
        } elseif($next == Token::T_CLASS) {
            return $this->parseClass($stream, '\\');
        } elseif($next == Token::T_FUNCTION) {
            $funcDetails = $this->parseFunction($stream, true);
            $funcName = $funcDetails[0];
            $funcArg = $funcDetails[1];
            return $this->compiler->compileFunction('\\' . $funcName, $funcArg);
        }
            
        $this->unexpectedToken($stream->current(), Token::T_NAMESPACE); 
    }
    
    public function parseNamespace(TokenStream $stream, $fromRoot = false) {
        $stream->decr();
        while($stream->next()->type() == Token::T_NAMESPACE) {
            $namespace[] = $stream->current()->value();
            $stream->nextIf(Token::T_NSSEPERATOR);
        }
        
        $namespace = implode('\\', $namespace) . '\\';
        if($fromRoot) {
            $namespace = '\\' . $namespace;
        }
        
        if($stream->current()->is(Token::T_CLASS)) {
            return $this->parseClass($stream, $namespace);
        }
        
        if($stream->current()->is(Token::T_FUNCTION)) {
            $funcDetails = $this->parseFunction($stream, true);
            $funcName = $funcDetails[0];
            $funcArg = $funcDetails[1];
            return $this->compiler->compileFunction($namespace . $funcName, $funcArg);
        }
        
        $this->unexpectedToken($stream->current(), Token::T_CLASS);       
    }

    public function parseClass(TokenStream $stream, $namespace = null) {
        $class = $stream->current()->value();
        
        if ($namespace) {
            $class = $namespace . $class;
        }

        $stream->nextIf(Token::T_CLASS_OPERATOR);

        if (($next = $stream->look()->type()) == Token::T_FUNCTION) {
            $method = $this->parseFunction($stream->incr(), true);
            return $this->compiler->compileStaticCall($class, $method[0], $method[1]);
        } elseif ($next == Token::T_VARIABLE) {
            return $this->compiler->compileStaticProperty($class, $stream->next()->value());
        } else {
            $this->unexpectedToken($stream->next(), Token::T_FUNCTION);
        }
    }
    
    public function parseMethod(TokenStream $stream, Variable $variable) {
        list($method, $args) = $this->parseFunction($stream, true);
        return $this->compiler->compileMethodCall($variable, $method, $args);
    }

    public function parseInlineFilter(TokenStream $stream, $content) {

        $stream->decr();
        while ($stream->next()->type() == Token::T_FILTER_PIPE) {
            $filter = $stream->nextIf(Token::T_FILTER)->value();
            $args = [];
            if ($stream->look()->type() == Token::T_OPARENT) {
                $args = $this->parseFunction($stream, true) [
                        1
                ];
            }
            $content = $this->compiler->compileFilter($filter, $content, $args);
        }

        $stream->decr();
        return $content;
    }

    public function parseFunction(TokenStream $stream, $returnRaw = false) {
        $func = $stream->current()->value();

        $stream->nextIf(Token::T_OPARENT);
        $args = [];
        while ($stream->next()->type() != Token::T_CPARENT) {
            $args[] = $this->parseExpr($stream);
            if ($stream->look()->type() == Token::T_COMMA) {
                $stream->incr();
            }
        }

        if ($returnRaw === true) {
            return [$func, $args];
        }
        return $this->compiler->compileFunction($func, $args);
    }

    public function parseForTag(TokenStream $stream) {
        $keyAlias = $this->parseExpr($stream->incr(), 2);
        if (!$keyAlias instanceOf Variable) {
            return $this->parsePhpforTag($stream, $keyAlias);
        }

        $keyAlias = $keyAlias->formatAttribute();
        $valueAlias = null;
        if ($stream->look()->type() == Token::T_COMMA) {
            $valueAlias = $this->parseExpr($stream->incr(2), 2);
            if (!$valueAlias instanceOf Variable) {
                $this->error("For loop tag value alias must be a valid variable", $stream->current()->line());
            }
            $valueAlias = $valueAlias->formatAttribute();
        }

        if ($stream->look()->type() == Token::T_VARIABLE) {
            $stream->nextIf(Token::T_VARIABLE, 'in');
        } else {
            $stream->nextIf(Token::T_IN);
        }

        $stream->next();
        $array = $this->parseExpr($stream);
        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $this->compiler->incrloopCount();
        $this->continueable++;
        $this->breakable++;
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EFOR && $parent != Token::T_END && $parent != Token::T_ELSE) {
            $content .= $this->parseToken($stream);
        }
        
        $this->continueable--;
        $this->breakable--;
        if ($parent == Token::T_ELSE) {
            return $this->compiler->compileForeach($array, $keyAlias, $valueAlias, $content) . $this->parseForelseTag($stream->incr());
        }
        
        $stream->incr(2);
        return $this->compiler->compileForeach($array, $keyAlias, $valueAlias, $content);
    }

    public function parsePhpforTag(TokenStream $stream, $expr1) {
        $stream->nextIf(Token::T_SCOLON);
        $expr2 = $this->parseExpr($stream->incr());
        $stream->nextIf(Token::T_SCOLON);
        $expr3 = $this->parseExpr($stream->incr());
        $stream->nextIf(Token::T_TAG_CLOSE, null, null, 'for');
        $this->compiler->incrIndent();
        $this->compiler->incrloopCount();
        $this->continueable++;
        $this->breakable++;
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EFOR && $parent != Token::T_END && $parent != Token::T_ELSE) {         
            $content .= $this->parseToken($stream);
        }
        
        $this->continueable--;
        $this->breakable--;
        if ($parent == Token::T_ELSE) {
            return $this->compiler->compileFor($expr1, $expr2, $expr3, $content) . $this->parseForelseTag($stream->incr());
        }

        $stream->incr(2);
        return $this->compiler->compileFor($expr1, $expr2, $expr3, $content);
    }

    public function parseForelseTag(TokenStream $stream) {
        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EFOR && $parent != Token::T_END) {
            $content .= $this->parseToken($stream);
        }

        $stream->incr(2);
        return $this->compiler->compileForelse($content);
    }

    public function parseIfTag(TokenStream $stream) {
        $expr = $this->parseExpr($stream->incr());
        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EIF && $parent != Token::T_END && $parent != Token::T_ELSEIF && $parent != Token::T_ELSE) {
            $content .= $this->parseToken($stream);
        }

        if ($parent == Token::T_ELSE) {
            return $this->compiler->compileIf($expr, $content) . $this->parseIfElseTag($stream->incr());
        }
        
        if ($parent == Token::T_ELSEIF) {
            return $this->compiler->compileIf($expr, $content) . $this->parseElseIfTag($stream->incr());
        }

        $stream->incr(2);
        return $this->compiler->compileIf($expr, $content);
    }

    public function parseElseIfTag(TokenStream $stream) {
        $expr = $this->parseExpr($stream->incr());
        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EIF && $parent != Token::T_END && $parent != Token::T_ELSEIF && $parent != Token::T_ELSE) {
            $content .= $this->parseToken($stream);
        }

        if ($parent == Token::T_ELSE) {
            return $this->compiler->compileElseif($expr, $content) . $this->parseIfElseTag($stream->incr());
        }
        
        if ($parent == Token::T_ELSEIF) {
            return $this->compiler->compileElseif($expr, $content) . $this->parseElseIfTag($stream->incr());
        }

        $stream->incr(2);
        return $this->compiler->compileElseif($expr, $content);
    }

    public function parseIfElseTag(TokenStream $stream) {
        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EIF && $parent != Token::T_END) {
            $content .= $this->parseToken($stream);
        }

        $stream->incr(2);
        return $this->compiler->compileElse($content);
    }

    public function parseTernaryExpr(TokenStream $stream, $expr) {
        $operator = $stream->current();
        if ($operator->type() == Token::T_TERNARY_OPERATOR) {
            $trueExpr = $expr;
            $falseExpr = $this->parseExpr($stream->incr());
        } else {
            $trueExpr = $this->parseExpr($stream->incr());
            if ($stream->look()->type() == Token::T_COLON) {
                $falseExpr = $this->parseExpr($stream->incr(2));
            } else {
                $falseExpr = "''";
            }
        }

        return $this->compiler->compileTernaryExpr($expr, $trueExpr, $falseExpr);
    }

    public function parseString(TokenStream $stream) {
        return $this->compiler->compileString($stream->current()->value());
    }

    public function parseNumber(TokenStream $stream) {
        return $this->compiler->compileNumber($stream->current()->value());
    }

    public function parsePrint(TokenStream $stream) {
        $expressions[] = $this->parseExpr($stream->incr());
        while ($stream->look()->type() == Token::T_COMMA) {
            $expressions[] = $this->parseExpr($stream->incr(2));
        }
        $stream->nextIf(Token::T_PRINT_CLOSE);
        return $this->compiler->compilePrint($expressions);
    }

    public function parseText(TokenStream $stream) {
        return $this->compiler->compileText($stream->current()->value());
    }

    public function parseOparent(TokenStream $stream) {
        $expr = $this->parseExpr($stream->incr());
        $stream->nextif(Token::T_CPARENT);
        return $this->compiler->compileExprGrouping($expr);
    }

    public function parseBracket(TokenStream $stream) {
        $args = [];
        while ($stream->next()->type() != Token::T_CBRACKETS) {
            $arg = $this->parseExpr($stream);
            if($stream->look()->type() == Token::T_ARRAY_OPERATOR) {
                $args[] = [$arg, $this->parseExpr($stream->incr(2))];
            } else {            
                $args[] = $arg;
            }
            
            if ($stream->look()->type() == Token::T_COMMA) {
                $stream->incr();
                continue;
            }
            $stream->nextIf(Token::T_CBRACKETS);
            break;
        }

        return $this->compiler->compileArray($args);
    }

    public function parseIncrDecrExpr(TokenStream $stream, $var = null) {
        $operator = $stream->current()->value();
        $isRight = true;
        if ($var === null) {
            $isRight = false;
            $var = $this->parseExpr($stream->incr());
        }
        
        if (!$var instanceOf Variable) {
            $this->error('Cannot decrement or increment non valid variable name', $stream->current()->line());
        }
        
        $var = $var->formatAttribute();
        return $this->compiler->compileIncrDecrExpr($var, $operator, $isRight);
    }
    
    public function parseAssignExpr(TokenStream $stream, $var) {
        if (!$var instanceOf Variable) {
                $this->error('Assignment expression expect the variable to assign to, to be a valid variable name', $stream->current()->line());
        }
        
        $var = $var->formatAttribute();      
        $operator = $stream->current()->value();               
        $varValue = $this->parseExpr($stream->incr());       
        return $this->compiler->compileAssignExpr($var, $operator, $varValue);
    }

    public function parseSetTag(TokenStream $stream) {
        $stream->nextIf(Token::T_VARIABLE);     
        $var = $this->parseVariable($stream);      
        $var = $var->formatAttribute();
        if($stream->look()->type() != Token::T_TAG_CLOSE) {
            $stream->nextIf(Token::T_ASSIGN);
            $varValue = $this->parseExpr($stream->incr());
            $stream->nextIf(Token::T_TAG_CLOSE);
            return $this->compiler->compileSet($var, $varValue);
        }
        
        $stream->nextIf(Token::T_TAG_CLOSE);
        $varValue = '';
        while (($parent = $stream->next()->parent()) != Token::T_ESET && $parent != Token::T_END) {
            $varValue .= $this->parseToken($stream);
        }

        $stream->incr(2);
        if (!empty($varValue)) {
            return $this->compiler->compileSet($var, $varValue, true);
        }

        return $this->compiler->compileSet($var, $varValue);
    }

    public function parseConcate(TokenStream $stream, $expr1) {        
        $args[] = $expr1;
        $stream->decr();
        while ($stream->look()->type() == Token::T_CONCATE) {
            $args[] = $this->parseExpr($stream->incr(2));
        }

        return $this->compiler->compileConcate($args);
    }
    
    public function parseMathsOperator(TokenStream $stream, $expr1, $op) {
        while (true) {        
            $expr2 = $this->parseExpr($stream->incr(), true);            
            $expr1 = $this->compiler->compileOperator($expr1, $op, $expr2);
            $nextToken = $stream->look();
            if(!$this->definitions->isMathsOperator($nextToken->type())) {
                break;
            }
            
            $op = $nextToken->value();
            $stream->incr();
        }

        return $expr1;
    }
    
    public function parseCompOperator(TokenStream $stream, $expr1, $op) {
        $expr2 = $this->parseExpr($stream->incr(), true);
        if($this->definitions->isComparisonOperator($stream->look()->value())) {
            $this->unexpectedToken($stream->next());
         }
         
         return $this->compiler->compileOperator($expr1, $op, $expr2);
    }
    
    public function parseLogicOperator(TokenStream $stream, $expr1, $op) {
        while (true) {        
            $expr2 = $this->parseExpr($stream->incr(), true);            
            $expr1 = $this->compiler->compileOperator($expr1, $op, $expr2);
            $nextToken = $stream->look();
            if(!$this->definitions->isLogicOperator($nextToken->type())) {
                break;
            }
            
            $op = $nextToken->value();
            $stream->incr();
        }

        return $expr1;
    }

    public function parseNot(TokenStream $stream) {
        $expr = $this->parseExpr($stream->incr());
        return $this->compiler->compileNotOperator($expr);
    }

    public function parseIncludeTag(TokenStream $stream) {
        $expr = $this->parseExpr($stream->incr());
        $data = '[]';
        $withCurrentData = true;
        if ($stream->nextIs(Token::T_VARIABLE, 'with')) {
            $data = $this->parseExpr($stream->incr(2));
        }

        if ($stream->nextIs(Token::T_VARIABLE, 'only')) {
            $stream->incr();
            $withCurrentData = false;
        }

        $stream->nextIf(Token::T_TAG_CLOSE);
        return $this->compiler->compileInclude($expr, $data, $withCurrentData);
    }

    public function parseWhileTag(TokenStream $stream) {
        $expr = $this->parseExpr($stream->incr());
        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $this->continueable++;
        $this->breakable++;
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EWHILE && $parent != Token::T_END) {
            $content .= $this->parseToken($stream);
        }
        
        $this->continueable--;
        $this->breakable--;
        $stream->incr(2);
        return $this->compiler->compileWhile($expr, $content);
    }

    public function parseSwitchTag(TokenStream $stream) {
        $expr = $this->parseExpr($stream->incr());

        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $hasDefault = false;
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_ESWITCH && $parent != Token::T_END) {
            if ($parent == Token::T_CASE) {
                $content .= $this->parseSwitchCaseTag($stream->incr());
                continue;
            }
            
            if ($parent == Token::T_DEFAULT) {
                if($hasDefault) {
                    $this->unexpectedToken($stream->next());
                }
                $hasDefault = true;
                $content .= $this->parseSwitchDefaultTag($stream->incr());
                continue;
            } 
            
            if($parent = Token::T_TEXT && empty(trim($stream->current()->value()))) {
                continue;
            }
            
            $this->unexpectedToken($stream->current());
        }
        $stream->incr(2);

        return $this->compiler->compileSwitch($expr, $content);
    }

    public function parseSwitchCaseTag(TokenStream $stream) {      
        $expr = $this->parseExpr($stream->incr());
        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $this->breakable++;
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_ESWITCH && $parent != Token::T_END && $parent != Token::T_CASE && $parent != Token::T_DEFAULT) {
            $content .= $this->parseToken($stream);
        }
        
        $this->breakable--;
        $stream->decr();
        return $this->compiler->compileCase($expr, $content);
    }

    public function parseSwitchDefaultTag(TokenStream $stream) {
        $stream->nextIf(Token::T_TAG_CLOSE);
        $this->compiler->incrIndent();
        $this->breakable++;
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_ESWITCH && $parent != Token::T_END && $parent != Token::T_CASE) {
            $content .= $this->parseToken($stream);
        }
        
        $this->breakable--;
        $stream->decr();
        return $this->compiler->compileDefault($content);
    }

    public function parseBreakTag(TokenStream $stream) {

        if ($this->breakable < 1) {
            $this->unexpectedToken($stream->current());
        }

        $expr = null;
        if ($stream->look()->type() != Token::T_TAG_CLOSE) {
            $expr = $this->parseExpr($stream->incr());
        }
        $stream->nextIf(Token::T_TAG_CLOSE);
        return $this->compiler->compileBreak($expr);
    }

    public function parseContinueTag(TokenStream $stream) {
        if ($this->continueable < 1) {
            $this->unexpectedToken($stream->current());
        }

        $expr = null;
        if ($stream->look()->type() != Token::T_TAG_CLOSE) {
            $expr = $this->parseExpr($stream->incr());
        }

        $stream->nextIf(Token::T_TAG_CLOSE);
        return $this->compiler->compileContinue($expr);
    }

    public function parseBlockTag(TokenStream $stream) {
        $stream->nextIf(Token::T_VARIABLE);
        $name = $this->parseString($stream);
        $oldIndent = $this->compiler->getIndent();
        $this->compiler->setIndent(3);

        if ($stream->look()->type() != Token::T_TAG_CLOSE) {
            $content = $this->compiler->compilePrint($this->parseExpr($stream->incr()));
            $stream->nextIf(Token::T_TAG_CLOSE);
        } else {
            $stream->next();
            $content = '';
            while (($parent = $stream->next()->parent()) != Token::T_EBLOCK && $parent != Token::T_END) {
                $content .= $this->parseToken($stream);
            }
            $stream->incr(2);
        }

        $this->compiler->setIndent($oldIndent);
        return $this->compiler->compileBlock($name, $content);
    }


    public function parseParentTag(TokenStream $stream) {
        $params = [];
        if ($stream->look()->type() != Token::T_TAG_CLOSE) {
            $params [$this->parseExpr($stream->incr())];
        }
        $stream->nextIf(Token::T_TAG_CLOSE);
        return $this->compiler->compilePrintRaw($this->compiler->compileFunction('parent', $params));
    }

    public function parseExtendsTag(TokenStream $stream) {
        if ($this->extendable === false || $stream->get(2) != $stream->current()) {
            $this->unexpectedToken($stream->current());
        }

        $this->extendable = false;
        $template = $this->parseExpr($stream->incr());
        $stream->nextIf(Token::T_TAG_CLOSE);
        $return = $this->compiler->compileExtends($template);
        while ($stream->next()->type() != Token::T_TEND) {
            if ($stream->current()->parent() == Token::T_BLOCK) {
                $this->parseToken($stream);
            }
        }
        $stream->decr();
        return $return;
    }

    public function parseRawTag(TokenStream $stream) {
        $expressions[] = $this->parseExpr($stream->incr());
        while ($stream->look()->type() == Token::T_COMMA) {
            $expressions[] = $this->parseExpr($stream->incr(2));
        }
        $stream->nextIf(Token::T_TAG_CLOSE);
        return $this->compiler->compilePrintRaw($expressions);
    }

    public function parseAutoescapeTag(TokenStream $stream) {
        $option = $stream->nextIf(Token::T_CONSTANT)->value();

        if ($option != 'true' && $option != 'false') {
            $this->unexpectedToken($stream->current(), Token::T_CONSTANT, 'true or false');
        }

        $stream->nextIf(Token::T_TAG_CLOSE);
        $escape = $this->compiler->getEscape();
        if ($option == 'true') {
            $this->compiler->onEscape();
        } else {
            $this->compiler->offescape();
        }

        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EAUTOESCAPE && $parent != Token::T_END) {
            $content .= $this->parseToken($stream);
        }
        $stream->incr(2);
        $this->compiler->setEscape($escape);
        return $content;
    }

    public function parseMacroTag(TokenStream $stream) {
        $name = $stream->nextIf(Token::T_FUNCTION);
        $stream->nextIf(Token::T_OPARENT);
        $args = [];
        while ($stream->next()->type() != Token::T_CPARENT) {
            $stream->decr();
            $arg = $stream->nextIf(Token::T_VARIABLE);
            if ($stream->look()->type() == Token::T_ASSIGN) {
                $stream->incr();
                $token = $stream->next();
                if ($token->type() != Token::T_STRING && $token->type() != Token::T_CONSTANT && $token->type() != Token::T_NUMERIC) {
                    $this->unexpectedToken($token);
                }
                if ($token->type() == Token::T_STRING) {
                    $args[] = [
                        $arg,
                        $this->parseString($stream)
                    ];
                } elseif ($token->type() == Token::T_NUMERIC) {
                    $args[] = [
                        $arg,
                        $this->parseNumber($stream)
                    ];
                } else {
                    $args[] = [
                        $arg,
                        $this->parseConstant($stream)
                    ];
                }
            } else {
                $args[] = $arg;
            }
            if ($stream->look()->type() != Token::T_CPARENT) {
                $stream->nextIf(Token::T_COMMA);
            }
        }

        $stream->nextIf(Token::T_TAG_CLOSE);
        $oldIndent = $this->compiler->getIndent();
        $this->compiler->setIndent(2);
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_EMACRO && $parent != Token::T_END) {
            $content .= $this->parseToken($stream);
        }
        $this->compiler->setIndent($oldIndent);
        $stream->incr(2);
        return $this->compiler->compileMacro($name, $args, $content);
    }

    public function parseImportTag(TokenStream $stream) {
        $token = $stream->look();
        $name = $this->parseExpr($stream->incr());
        if ($stream->look()->value() == 'as') {
            $stream->nextIf(Token::T_VARIABLE);
            $as = $this->parseExpr($stream->incr());
            if (!$as instanceOf Variable) {
                $this->error("Import tag 'as' alias must be a valid variable name", $stream->current()->line());
            }

            $as = $as->formatAttribute();
        }

        if (!isset($as)) {
           if($stream->current() !== $token || $token->type() != Token::T_STRING) {
               $this->error("Undefined alias variable for the import tag", $token->line());
           }
           
           $as = $token->value();
           $nsOpPos = strrpos($as, '::');
           $dotPos = strpos($as, '.');
           $slashPos = strrpos($as, '/');
           if ($nsOpPos && $nsOpPos > $slashPos) {
               $slashPos = ++$nsOpPos;
           }
           
           if ($slashPos !== false) {
               if ($dotPos && $slashPos < $dotPos) {
                   $as = substr($as, ++$slashPos, $dotPos - $slashPos);
               } else {
                   $as = substr($as, ++$slashPos);
               }
           } elseif ($dotPos) {
                   $as = substr($as, 0, $dotPos);
           }
            $as = $this->compiler->formatAttribute([[$as, '[var']]);
   }
    $stream->nextIf(Token::T_TAG_CLOSE);

        return $this->compiler->compileImport($name, $as);
    }

    public function parseDoTag(TokenStream $stream) {
        $expressions[] = $this->parseExpr($stream->incr());
        while ($stream->look()->type() == Token::T_COMMA) {
            $expressions[] = $this->parseExpr($stream->incr(2));
        }
        $stream->nextIf(Token::T_TAG_CLOSE);
        return $this->compiler->compileExpr($expressions);
    }

    public function parseETag(TokenStream $stream) {
        $expressions[] = $this->parseExpr($stream->incr());
        while ($stream->look()->type() == Token::T_COMMA) {
            $expressions[] = $this->parseExpr($stream->incr(2));
        }
        $stream->nextIf(Token::T_TAG_CLOSE);
        return $this->compiler->escapePrint($expressions);
    }

    public function parseTest(TokenStream $stream, $item) {
        $test = $stream->nextIf(Token::T_VARIABLE)->value();
        if ($test == 'set') {
            $test = 'isset';            
        }
        
        return $this->compiler->compileFunction($test, [$item]);
    }

    public function parseIn(TokenStream $stream, $needle) {
        $haystack = $this->parseExpr($stream->incr());
        return $this->compiler->compileFunction('in', [$needle, $haystack]);
    }

    public function parseFilterTag(TokenStream $stream) {
        if (($type = $stream->next()->type()) != Token::T_VARIABLE && $type != Token::T_FUNCTION) {
            $this->unexpectedToken($stream->current());
        }
        $filter = $stream->current()->value();
        $args = [];
        if ($stream->look()->type() == Token::T_OPARENT) {
            $args = $this->parseFunction($stream, true) [
                    1
            ];
        }
        $filters[$filter] = $args;
        while ($stream->next()->type() == Token::T_FILTER_PIPE) {
            $filter = $stream->nextIf(Token::T_FILTER)->value();
            $args = [];
            if ($stream->look()->type() == Token::T_OPARENT) {
                $args = $this->parseFunction($stream, true) [
                        1
                ];
            }

            $filters[$filter] = $args;
        }
        $stream->decr();
        $stream->nextIf(Token::T_TAG_CLOSE);

        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_ETFILTER && $parent != Token::T_END) {
            $content .= $this->parseToken($stream);
        }
        $stream->incr(2);
        return $this->compiler->compileTagFilter($filters, $content);
    }

    public function parseSpacelessTag(TokenStream $stream) {
        $stream->nextIf(Token::T_TAG_CLOSE);
        $content = '';
        while (($parent = $stream->next()->parent()) != Token::T_ESPACELESS && $parent != Token::T_END) {
            $content .= $this->parseToken($stream);
        }
        $stream->incr(2);

        return $this->compiler->compileSpaceless($content);
    }

}