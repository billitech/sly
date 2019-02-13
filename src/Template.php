<?php

namespace Billitech\Sly;

use Exception;
use Billitech\Sly\Exceptions\RuntimeError;
use Billitech\Sly\Exceptions\LoadError;

abstract class Template implements TemplateInterface {

    protected $sly;
    protected $blocks = [];
    protected $parent;

    public function __construct(SlyInterface $sly) {
        $this->sly = $sly;
        $this->initialize();
    }
    
    abstract public function initialize();
    
    abstract public function display(Array $data = [], Array $blocks = []);

    public function loadTemplate($template, $line) {
        try {
            return $this->sly->loadTemplate($template);
        } catch (LoadError $e) {
            $this->error("Unable to load template '{$template}'", $line);
        }
    }

    public function error($message, $line) {
        throw new RuntimeError($message . " at line '{$line}' in file '{$this->getTemplateName()}'.");
    }

    public function escape($content) {
        return Functions::escape($content);
    }

    public function getVar($data, $var, $line = null) {
        return isset($data[$var]) ? $data[$var] : null;
    }

    public function setVar(&$data, $var, $value, $line = null) {
        $data[$var] = $value;
    }

    public function renderBlock($block, Array $data = [], $blocks = []) {
        ob_start();
        if (isset($blocks[$block])) {
            $blocks[$block]($data, $blocks);
        } else {
            $this->blocks[$block]($data, $blocks);
        }
        return ltrim(ob_get_clean());
    }

    public function renderParentBlock($block, array $data, $line) {
        if ($block === null) {
            $this->error("Cannot call 'parent' function outside block without passing block name as first parameter", $line);
        }

        $parent = $this->getParent();
        if (!$parent instanceOf TemplateInterface) {
            $this->error("Parent template is not an instance of TemplateInterface", $line);
        }

        if ($parent->hasBlock($block)) {
            return $this->getParent()->renderBlock($block, $data);
        }

        return $parent->renderParentBlock($block, $data, $line);
    }

    public function hasBlock($block) {
        return isset($this->blocks[$block]);
    }

    public function ensureTraversable($item) {
        return (is_array($item) || $item instanceof \Traversable) ? $item : [];
    }

    public function getAttribute($item, $attribute, $line) {
        if (is_array($item) && array_key_exists($attribute, $item)) {
            return $item[$attribute];
        } elseif ($item instanceof \ArrayAccess) {
            return $item[$attribute];
        } elseif (is_object($item)) {

            return $item->$attribute;
        }

        $this->error("Cannot access attribute '{$attribute}' on a non array or object", $line);
    }

    public function call($callable, $parameters, $line) {
        if (is_string($callable) && $this->sly->isFunction($callable)) {
            return $this->sly->callFunction($callable, $parameters);
        }

        if (is_array($callable) && $callable[0] instanceOf self) {
            if (method_exists($callable[0], ($method = 'macro' . ucwords($callable[1])))) {
                $callable[1] = $method;
                return $this->renderMacro($callable, $parameters);
            }
        }

        if (is_callable($callable)) {
            return call_user_func_array($callable, $parameters);
        }

        $type = (is_string($callable)) ? $callable : getType($callable);
        $this->error("Call to undefined function|callable '{$type}'", $line);
    }

    public function renderMacro($callable, $parameters) {
        $obLevel = ob_get_level();
        ob_start();
        try {
            call_user_func_array($callable, $parameters);
        } catch (Exception $e) {
            $this->handleException($e, $obLevel);
        }
        return ltrim(ob_get_clean());
    }

    public function render($data, $blocks = []) {
        $obLevel = ob_get_level();
        ob_start();
        try {
            $this->display($data, $blocks);
        } catch (Exception $e) {
            $this->handleException($e, $obLevel);
        }
        return ltrim(ob_get_clean());
    }

    protected function handleException($e, $obLevel) {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
        throw $e;
    }

    public function getParent() {
        return $this->parent;
    }

}