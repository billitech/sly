<?php

namespace Billitech\Sly;

interface CompilerInterface {
    
    public function newInstance(Parser $parser);

    public function toClass($code);

    public function compileIf($expr, $content);

    public function CompileElseif($expr, $content);

    public function CompileElse($content);

    public function compileForeach($array, $keyAlias, $valueAlias, $content);

    public function compileFor($start, $expr, $incr, $content);

    public function compileForelse($content);

    public function compilePrint($content);

    public function CompilePrintRaw($content);

    public function compileText($content);

    public function compileString($string);
    
    public function compileNumber($number);

    public function compileVariable(Array $attributes, $line);

    public function compileConstant($constant);
    
    public function compileExprGrouping($expr);
    
    public function compileArray($args);
    
    public function compileAssignExpr($var, $operator, $value);

    public function compileSet($var, $value, $isCapture = false);

    public function compileIncrDecrExpr($var, $operator, $isRight = true);

    public function compileFunction($func, $parameters);

    public function compileMethodCall($object, $method, $parameters);

    public function compileFilter($filter, $content, $parameters);

    public function compileTagFilter(Array $filters, $content);

    public function compileStaticCall($class, $method, $parameters);

    public function compileStaticProperty($class, $property);

    public function compileConcate($parameters);
    
    public function compileOperator($leftExpr, $operator, $rightExpr);
    
    public function compileNotOperator($expr);

    public function compileInclude($expr, $data, $withCurrentData);

    public function compileWhile($expr, $content);

    public function compileSwitch($expr, $content);

    public function compileCase($expr, $content);

    public function compileDefault($content);

    public function compileBreak($expr = null);

    public function compileContinue($expr = null);

    public function compileBlock($name, $content);

    public function compileExtends($template);

    public function formatAttribute(Array $attribute);

    public function compileIsset($variable);

    public function compileEmpty($variable);

    public function compileMacro($name, Array $args, $content);

    public function compileImport($template, $alias);

    public function compileExpr($expressions);

    public function escapePrint($content);

    public function compileSpaceless($content);

    public function compileError($message, $line);
    
    public function compileTernaryExpr($expr, $trueExpr, $falseExpr);

    function addslashes($string);

    public function onEscape();

    public function offEscape();

    public function defaultEscape();

    public function setEscape($option);

    public function getEscape();

    public function loopCountVar($addVarSign = true);

    public function incrLoopCount();

    public function decrLoopCount();

    public function indent();

    public function getIndent();

    public function setIndent($num);

    public function decrIndent($number = 1);

    public function incrIndent($number = 1);

    public function setStream(TokenStream $stream);

    public function setParser(ParserInterface $parser);
}