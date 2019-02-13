<?php

namespace Billitech\Sly;

interface TemplateInterface {
    public function display(Array $data = [], Array $blocks = []);

    public function loadTemplate($template, $line);

    public function error($message, $line);

    public function escape($content);

    public function getVar($data, $var, $line = null);

    public function setVar(&$data, $var, $value, $line = null);

    public function renderBlock($block, Array $data = [], $blocks = []);

    public function renderParentBlock($block, array $data, $line);

    public function hasBlock($block);

    public function getAttribute($item, $attribute, $line);

    public function call($callable, $parameters, $line);

    public function renderMacro($callable, $parameters);

    public function render($data, $blocks = []);

    public function getParent();
}
