<?php

namespace Billitech\Sly;

class Compiler implements CompilerInterface {

    /**
     * The sly instance.
     *
     * @var SlyInterface
     */
    public $sly;

    /**
     * The token stream instance.
     *
     * @var TokenStream
     */
    public $stream;

    /**
     * The parser instance.
     *
     * @var ParserInterface
     */
    public $parser;
    public $parentTemplate = null;
    public $indent = 2;
    public $loopCount = 0;
    public $newLine = "\n";
    public $escape = 'default';
    public $blocks;
    public $macros;
    public $inIsset = 0;

    public function __construct(Sly $sly) {
        $this->sly = $sly;
    }
    
    public function newInstance(Parser $parser) {
        $instance = new Compiler($this->sly);
        $instance->setParser($parser);
        $instance->setStream($parser->getStream());
        return $instance;
    }

    public function toClass($code) {
        $code = '<?php
        
class ' . $this->sly->getTemplateClass($this->stream->filename()) . ' extends '.$this->sly->getTemplatesBaseClass().' {
    
    public function initialize() {' . ($this->parentTemplate !== null ? '
        $this->parent = $this->loadTemplate(' . $this->parentTemplate . ', 1);' : '') . (!empty($this->blocks) ? $this->blocks : '') . '        
    }
    
    public function display(Array $data = [], Array $blocks = []) {' . $code . '
    }
    ' . $this->macros . '
    public function getTemplateName() {
        return ' . "'" . $this->stream->filename() . "'" . ';
    }    
     
    public function getCompileTime() {
        return ' . "'" . date('Y-m-d H:i:s') . "'" . ';
    }
    
}';
        return $code;
    }

    public function compileIf($expr, $content) {
        $this->indent--;
        return $this->indent() . 'if( ' . $expr . ' ) {' . $content . $this->indent() . '}';
    }

    public function compileElseif($expr, $content) {
        $this->indent--;
        return ' elseif( ' . $expr . ' ) {' . $content . $this->indent() . '}';
    }

    public function compileElse($content) {
        $this->indent--;
        return ' else {' . $content . $this->indent() . '}';
    }

    public function compileForeach($array, $keyAlias, $valueAlias, $content) {
        $alias = (($valueAlias === null) ? $keyAlias : $keyAlias . ' => ' . $valueAlias);

        $array = '$this->ensureTraversable(' . $array . ')';
        $this->indent--;
        $out = $this->indent() . $this->loopCountVar() . ' = 0;' . $this->indent() . 'foreach( ' . $array . ' as ' . $alias . ' ) {';
        $this->indent++;
        $out .= $this->indent() . '$data[\'loopCount\'] = ++' . $this->loopCountVar() . ';';
        $this->indent--;
        $this->loopCount--;
        return $out . $content . $this->indent() . '}';
    }

    public function compileFor($expr1, $expr2, $expr3, $content) {
        $this->indent--;
        $out = $this->indent() . $this->loopCountVar() . ' = 0;' . $this->indent() . 'for( ' . $expr1 . '; ' . $expr2 . '; ' . $expr3 . ' ) {';
        $this->indent++;
        $out .= $this->indent() . '$data[\'loopCount\'] = ++' . $this->loopCountVar() . ';';
        $this->indent--;
        $this->loopCount--;
        return $out . $content . $this->indent() . '}';
    }

    public function compileForelse($content) {
        $this->loopCount++;
        $out = $this->compileIf($this->loopCountVar() . ' == 0', $content);
        $this->loopCount--;
        return $out;
    }

    public function compilePrint($content) {
        if ($this->escape === 'default') {
            return $this->indent() . 'echo $this->escape(' . implode(') . $this->escape(', (array)$content) . ');';
        } elseif ($this->escape === false) {
            return $this->indent() . 'echo ' . implode(' . ', (array)$content) . ';';
        } else {
            return $this->indent() . 'echo $this->escape(' . implode(', true) . $this->escape(', (array)$content) . ', true);';
        }
    }

    public function compilePrintRaw($content) {
        return $this->indent() . 'echo ' . implode(' . ', (array)$content) . ';';
    }

    public function compileText($content) {
        return $this->indent() . "echo '" . $this->addslashes($content) . "';";
    }

    public function compileString($string) {
        return "'" . $this->addslashes($string) . "'";
    }
    
    public function compileNumber($number) {
        return $number;
    }

    public function compileVariable(Array $attributes, $line) {
        if ($attributes[0][0] == 'this') {
            $var = '$this';
        } else {
            $var = '$this->getVar($data, \'' . $attributes[0][0] . '\', ' . $line . ')';
        }
        unset($attributes[0]);
        
        foreach ($attributes as $item) {
            if($item[1] == '[var' || $item[1] == '->') {
                $var = "\$this->getAttribute($var, '$item[0]', $line)";
            } else {
                $var = "\$this->getAttribute($var, $item[0], $line)";
            }            
        }
        
        return $var; 
    }

    public function compileConstant($constant) {
        return $constant;
    }
    
    public function compileExprGrouping($expr) {
        return "({$expr})";
    }
    
    public function compileArray($args) {
        $argsCount = count($args);
        for($i = 0; $i < $argsCount; $i++) {
            if(is_array($args[$i])) {
                $args[$i] = "{$args[$i][0]} => {$args[$i][1]}";
            }
        }
        return '[' . implode(', ', $args) . ']';
    }
    
    public function compileAssignExpr($var, $operator, $value) {
         return "({$var} {$operator} {$value})";
    }

    public function compileSet($var, $value, $isCapture = false) {

        if ($isCapture == true) {
            return $this->indent() . 'ob_start();' . $value . $this->indent() . $var . ' = ob_get_clean();';
        }

        return $this->indent() . $var . ' = ' . $value . ';';
    }

    public function compileIncrDecrExpr($var, $operator, $isRight = true) {
        if ($isRight) {
            $var = $var . $operator;
        } else {
            $var = $operator . $var;
        }

        return '(' . $var . ')';
    }

    public function compileFunction($func, $parameters) {
        if ($this->sly->isCompileTimeFunction($func)) {
            return call_user_func_array(
                    $this->sly->getCompileTimeFunction($func), [
                $parameters,
                $this->stream,
                $this->parser,
                $this   
                    ]
            );
        }

        $parameters = '[' . implode(', ', $parameters) . ']';

        return '$this->call(' . "'" . $func . "', " . $parameters . ', ' . $this->stream->current()->line() . ')';
    }

    public function compileMethodCall($object, $method, $parameters) {

        $parameters = '[' . implode(', ', $parameters) . ']';

        return '$this->call([' . $object . ", '" . $method . "'], " . $parameters . ', ' . $this->stream->current()->line() . ')';
    }

    public function compileFilter($filter, $content, $parameters) {
        array_unshift($parameters, $content);

        return $this->compileFunction($filter, $parameters);
    }

    public function compileTagFilter(Array $filters, $content) {
        $return = $this->indent() . 'ob_start();' . $content;
        $content = 'ob_get_clean()';
        foreach ($filters as $filter => $parameters) {
            $content = $this->compileFilter($filter, $content, $parameters);
        }
        return $return . $this->indent() . 'echo ' . $content . ';';
    }

    public function compileStaticCall($class, $method, $parameters) {
        $parameters = '[' . implode(', ', $parameters) . ']';

        return '$this->call(' . "'" . $class . "::" . $method . "', " . $parameters . ', ' . $this->stream->current()->line() . ')';
    }

    public function compileStaticProperty($class, $property) {
        return $class . '::$' . $property;
    }

    public function compileConcate($parameters) {
        return implode($parameters, ' . ');
    }
    
    public function compileOperator($leftExpr, $operator, $rightExpr) {
        return "{$leftExpr} {$operator} {$rightExpr}";
    }
    
    public function compileNotOperator($expr) {
        return "!{$expr}";
    }

    public function compileInclude($expr, $data, $withCurrentData = true) {
        $data = ($withCurrentData === true) ? 'array_merge($data, (array)' . $data . ')' : '(array)' . $data;

        return $this->indent() . 'echo $this->loadTemplate(' . $expr . ', ' . $this->stream->current()->line() . ')->render(' . $data . ');';
    }

    public function compileWhile($expr, $content) {
        $this->indent--;

        return $this->indent() . 'while( ' . $expr . ' ) {' . $content . $this->indent() . '}';
    }

    public function compileSwitch($expr, $content) {
        $this->indent--;

        return $this->indent() . 'switch( ' . $expr . ' ) {' . $content . $this->indent() . '}';
    }

    public function compileCase($expr, $content) {
        $this->indent--;
        return $this->indent() . 'case ' . $expr . ' :' . $content;
    }

    public function compileDefault($content) {
        $this->indent--;
        return $this->indent() . 'default :' . $content;
    }

    public function compileBreak($expr = null) {
        if ($expr === null) {
            return $this->indent() . 'break;';
        } else {
            return $this->indent() . 'break ' . $expr . ';';
        }
    }

    public function compileContinue($expr = null) {
        if ($expr === null) {
            return $this->indent() . 'continue;';
        } else {
            return $this->indent() . 'continue ' . $expr . ';';
        }
    }

    public function compileBlock($name, $content) {
        $this->blocks .= $this->newLine . '        $this->blocks[' . $name . '] = function(Array $data = [], Array $blocks = []) {
            $blockName = ' . $name . ';' . $content . '
        };';

        return $this->indent() . 'echo $this->renderBlock(' . $name . ', $data, $blocks);';
    }

    public function compileExtends($template) {
        $this->parentTemplate = $template;
        return $this->indent() . 'echo $this->parent->render($data, array_merge($this->blocks, $blocks));';
    }

    public function formatAttribute(Array $attribute) {
        $out = '$data';
        foreach ($attribute as $item) {
            switch($item[1]) {
                case '[var' :
                    $out .= "['$item[0]']";
                break;
                case '[' :
                    $out .= "[$item[0]]";
                break;
                default :
                    $out .= "->$item[0]";
            }
            
        }
        return $out;
    }

    public function compileIsset($variable) {
        return 'isset(' . $variable . ')';
    }

    public function compileEmpty($variable) {
        return 'empty(' . $variable . ')';
    }

    public function compileMacro($name, Array $args, $content) {
        $formatedArgs = '';
        $dataSet = '        $data = [';
        $argsCount = count($args);
        $i = 0;
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $formatedArgs .= '$' . $arg[0] . ' = ' . $arg[1];
                $dataSet .= $this->newLine . '            \'' . $arg[0] . '\' => $' . $arg[0];
            } else {
                $formatedArgs .= '$' . $arg . ' = null';
                $dataSet .= $this->newLine . '            \'' . $arg . '\' => $' . $arg;
            }

            if (++$i != $argsCount) {
                $formatedArgs .= ', ';
                $dataSet .= ',';
            }
        }
        $dataSet .= ($i == 0) ? '];' : $this->newLine . '        ];';
        $this->macros .= $this->newLine . '    public function macro' . ucwords($name) . '(' . $formatedArgs . ') {
' . $dataSet . $content . '
    }' . $this->newLine;
    }

    public function compileImport($template, $alias) {
        return $this->indent() . $alias . ' = $this->loadTemplate(' . $template . ', ' . $this->stream->current()->line() . ');';
    }

    public function compileExpr($expressions) {        
        return $this->indent() . implode(";{$this->indent()}", (array)$expressions) . ';';
    }

    public function escapePrint($content) {
        return $this->indent() . 'echo $this->escape(' . implode(' . ', (array)$content) . ', true);';
    }

    public function compileSpaceless($content) {
        return $this->indent() . 'ob_start();' . $content . $this->indent() . "echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));";
    }

    public function compileError($message, $line) {
        return '$this->error(' . $message . ', ' . $line . ');';
    }
    
    public function compileTernaryExpr($expr, $trueExpr, $falseExpr) {
        return $expr . ' ? ' . $trueExpr . ' : ' . $falseExpr;
    }

    public function addslashes($string) {
        return str_replace(array("\\", "'"), array("\\\\", "\\'"), $string);
    }

    public function onEscape() {
        $this->escape = true;
        return $this;
    }

    public function offEscape() {
        $this->escape = false;
        return $this;
    }

    public function defaultEscape() {
        $this->escape = 'default';
        return $this;
    }

    public function setEscape($option) {
        $this->escape = $option;
        return $this;
    }

    public function getEscape() {
        return $this->escape;
    }

    public function loopCountVar($addVarSign = true) {
        if ($this->loopCount < 2) {
            return (($addVarSign == true) ? '$' : '') . 'loopCount';
        }
        return (($addVarSign == true) ? '$' : '') . 'loopCount' . $this->loopCount;
    }

    public function incrLoopCount() {
        $this->loopCount++;
        return $this;
    }

    public function decrLoopCount() {
        $this->loopCount--;
        return $this;
    }

    public function indent() {
        return $this->newLine . str_repeat(' ', $this->indent * 4);
    }

    public function getIndent() {
        return $this->indent;
    }

    public function setIndent($num) {
        $this->indent = (int) $num;
        return $this;
    }

    public function decrIndent($number = 1) {
        $this->indent -= $number;
        return $this;
    }

    public function incrIndent($number = 1) {
        $this->indent += $number;
        return $this;
    }

    public function setStream(TokenStream $stream) {
        $this->stream = $stream;
        return $this;
    }

    public function setParser(ParserInterface $parser) {
        $this->parser = $parser;
        return $this;
    }

}