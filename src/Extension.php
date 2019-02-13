<?php

namespace Billitech\Sly;

abstract class Extension implements ExtensionInterface {

    protected $tags = [];
    protected $parsers = [];
    protected $globals = [];
    protected $functions = [];
    protected $compileTimeFunctions = [];

    public function __construct() {
        $this->setTags();
        $this->setParsers();
        $this->setGlobals();
        $this->setFunctions();
        $this->setCompileTimeFunctions();
    }

    public function getTags() {
        return $this->tags;
    }

    public function getParsers() {
        return $this->parsers;
    }

    public function getGlobals() {
        return $this->globals;
    }

    public function getFunctions() {
        return $this->functions;
    }

    public function getCompileTimeFunctions() {
        return $this->compileTimeFunctions;
    }

    public function setTags() {
        
    }

    public function setParsers() {
        
    }

    public function setGlobals() {
        
    }

    public function setFunctions() {
        
    }

    public function setCompileTimeFunctions() {
        
    }

}
