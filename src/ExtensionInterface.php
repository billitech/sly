<?php

namespace Billitech\Sly;

interface ExtensionInterface {

    public function getTags();

    public function getParsers();

    public function getGlobals();

    public function getFunctions();

    public function getCompileTimeFunctions();
}
