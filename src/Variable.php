<?php

namespace Billitech\Sly;

class Variable {

    protected $attribute;
    protected $line;
    protected $compiler;

    public function __construct(Array $attribute, $line, CompilerInterface $compiler) {
        $this->attribute = $attribute;
        $this->line = $line;
        $this->compiler = $compiler;
    }

    public function getAttribute() {
        return $this->attribute;
    }

    public function getLine() {
        return $this->line;
    }

    public function getCompiler() {
        return $this->compiler;
    }

    public function formatAttribute() {
        return $this->compiler->formatAttribute($this->attribute);
    }

    public function __toString() {
        return $this->compiler->compileVariable($this->attribute, $this->line);
    }

}
